"""Feature engineering: build per-animal feature dicts at a given timestamp."""
from __future__ import annotations

from datetime import datetime, timedelta
from typing import Dict

import pandas as pd

KNOWN_SPECIES = ["vache", "chien", "chat", "mouton", "poule", "cheval", "lapin"]


def _normalize_species(value) -> str:
    if not isinstance(value, str):
        return "unknown"
    v = value.strip().lower()
    return v if v in KNOWN_SPECIES else "other"


def _safe_float(value, default: float = 0.0) -> float:
    try:
        if value is None or pd.isna(value):
            return default
        return float(value)
    except (TypeError, ValueError):
        return default


def build_features(
    animal_row: pd.Series,
    feedings: pd.DataFrame,
    at,
) -> Dict[str, float]:
    """
    Build a feature dict for one animal at instant `at`, using only data available before `at`.

    `at` may be a datetime or pd.Timestamp; we normalize to pd.Timestamp so all
    comparisons use the same type as the parsed dataframe columns.
    """
    feats: Dict[str, float] = {}
    at = pd.Timestamp(at)

    birth = animal_row.get("date_naissance")
    if pd.notna(birth):
        feats["age_days"] = max(0.0, (at - pd.Timestamp(birth)).days)
    else:
        feats["age_days"] = 0.0

    feats["weight"] = _safe_float(animal_row.get("weight"))

    own = feedings[feedings["animal_id"] == animal_row["id"]]
    own = own[own["feeding_date"] < at]

    win7 = own[own["feeding_date"] >= at - pd.Timedelta(days=7)]
    win30 = own[own["feeding_date"] >= at - pd.Timedelta(days=30)]

    feats["feedings_7d"] = float(len(win7))
    feats["feedings_30d"] = float(len(win30))
    feats["avg_qty_fed_7d"] = float(win7["quantity_fed"].astype(float).mean()) if len(win7) else 0.0
    feats["avg_qty_fed_30d"] = float(win30["quantity_fed"].astype(float).mean()) if len(win30) else 0.0

    if len(own):
        last = own["feeding_date"].max()
        feats["days_since_last_feeding"] = max(0.0, (at - pd.Timestamp(last)).days)
    else:
        feats["days_since_last_feeding"] = 999.0

    sp = _normalize_species(animal_row.get("species"))
    for known in KNOWN_SPECIES + ["other"]:
        feats[f"species_{known}"] = 1.0 if sp == known else 0.0

    return feats
