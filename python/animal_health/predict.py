"""Predict health-degradation risk for every active animal and write predictions.json."""
from __future__ import annotations

import json
from datetime import datetime
from pathlib import Path

import click
import pandas as pd

from .db import fetch_animals, fetch_feedings
from .features import build_features
from .model import load_model

ROOT = Path(__file__).resolve().parent.parent
OUTPUT = ROOT / "var" / "predictions.json"


def _risk_label(p: float) -> str:
    if p >= 0.66:
        return "high"
    if p >= 0.33:
        return "medium"
    return "low"


@click.command()
@click.option("--top", default=0, show_default=True,
              help="Keep only the top N riskiest animals (0 = all).")
def main(top: int) -> None:
    model = load_model()
    animals = fetch_animals()
    feedings = fetch_feedings()

    active = animals[animals["deleted_at"].isna()].copy()
    if active.empty:
        click.echo("No active animals.")
        OUTPUT.parent.mkdir(parents=True, exist_ok=True)
        OUTPUT.write_text("[]", encoding="utf-8")
        return

    now = pd.Timestamp.now()
    rows = []
    for _, animal in active.iterrows():
        x = build_features(animal, feedings, now)
        proba = model.predict_proba_one(x) or {}
        risk = float(proba.get(1, 0.0))
        rows.append({
            "animal_id": int(animal["id"]),
            "name": animal.get("name"),
            "species": animal.get("species"),
            "current_health": animal.get("health_status"),
            "risk_score": round(risk, 4),
            "risk_label": _risk_label(risk),
            "predicted_at": now.isoformat(timespec="seconds") if hasattr(now, "isoformat") else str(now),
        })

    rows.sort(key=lambda r: r["risk_score"], reverse=True)
    if top > 0:
        rows = rows[:top]

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(json.dumps(rows, indent=2, ensure_ascii=False), encoding="utf-8")

    click.echo(f"Wrote {len(rows)} predictions to {OUTPUT}")
    for r in rows[:10]:
        click.echo(f"  #{r['animal_id']:>4}  {r['name']:<20}  {r['risk_label']:<6}  {r['risk_score']:.3f}")


if __name__ == "__main__":
    main()
