"""Walk every animal's timeline, build (features, label) samples, and incrementally train."""
from __future__ import annotations

from datetime import datetime, timedelta

import click
import pandas as pd
from river import metrics

from .db import fetch_animals, fetch_feedings, fetch_history
from .features import build_features
from .labels import degraded_within
from .model import load_model, save_model


def _observation_dates(animal_row: pd.Series, step_days: int) -> list[pd.Timestamp]:
    now = pd.Timestamp.now().normalize()
    start = animal_row.get("date_naissance")
    if pd.isna(start):
        start = now - pd.Timedelta(days=180)
    end = animal_row.get("deleted_at")
    if pd.isna(end):
        end = now
    if end <= start:
        return []

    dates: list[pd.Timestamp] = []
    cur = pd.Timestamp(start).normalize()
    last = pd.Timestamp(end).normalize() - pd.Timedelta(days=step_days)
    while cur <= last:
        dates.append(cur)
        cur += pd.Timedelta(days=step_days)
    return dates


@click.command()
@click.option("--step-days", default=7, show_default=True,
              help="Generate one observation every N days of an animal's life.")
@click.option("--horizon-days", default=30, show_default=True,
              help="Look-ahead window used to label degradation.")
@click.option("--reset/--no-reset", default=False,
              help="Discard the existing model file and start from scratch.")
def main(step_days: int, horizon_days: int, reset: bool) -> None:
    animals = fetch_animals()
    feedings = fetch_feedings()
    history = fetch_history()

    click.echo(f"Loaded {len(animals)} animals, {len(feedings)} feedings, {len(history)} history rows.")
    if animals.empty:
        click.echo("No animals — nothing to train on.")
        return

    if reset:
        from .model import new_model
        model = new_model()
        click.echo("-> starting from a fresh model.")
    else:
        model = load_model()

    acc = metrics.Accuracy()
    f1 = metrics.F1()
    n_samples = 0
    n_positive = 0

    for _, animal in animals.iterrows():
        for at in _observation_dates(animal, step_days):
            x = build_features(animal, feedings, at)
            y = degraded_within(int(animal["id"]), history, animal, at, horizon_days)

            y_pred = model.predict_one(x) or 0
            acc.update(y, y_pred)
            f1.update(y, y_pred)
            model.learn_one(x, y)

            n_samples += 1
            n_positive += y

    save_model(model)

    click.echo(f"Trained on {n_samples} samples ({n_positive} positives).")
    click.echo(f"Running accuracy: {acc}")
    click.echo(f"Running F1:       {f1}")
    click.echo("Model saved to var/animal_health_model.pkl")


if __name__ == "__main__":
    main()
