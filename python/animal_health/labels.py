"""Derive a binary label per (animal, observation_date).

Definition: 1 if the animal's health degraded OR was archived (deleted_at set)
within the next `horizon_days` after the observation date, else 0.
"""
from __future__ import annotations

import json
from datetime import timedelta

import pandas as pd

# Lower index = better health (subject to your domain — adjust as needed).
HEALTH_RANK = {
    "Excellente": 0, "Bonne": 1, "Moyenne": 2, "Faible": 3, "Mauvaise": 4, "Critique": 5,
    "OK": 1, "Sick": 4, "Healthy": 1,
}


def _rank(status) -> int | None:
    if not isinstance(status, str):
        return None
    return HEALTH_RANK.get(status.strip())


def degraded_within(
    animal_id: int,
    history: pd.DataFrame,
    animal_row: pd.Series,
    at: pd.Timestamp,
    horizon_days: int = 30,
) -> int:
    end = at + timedelta(days=horizon_days)

    # Archived (soft delete) within horizon → strong negative outcome.
    deleted_at = animal_row.get("deleted_at")
    if pd.notna(deleted_at) and at <= deleted_at <= end:
        return 1

    rows = history[
        (history["animal_id"] == animal_id)
        & (history["created_at"] > at)
        & (history["created_at"] <= end)
    ].sort_values("created_at")

    current = _rank(animal_row.get("health_status"))
    if current is None:
        return 0

    for _, row in rows.iterrows():
        snap = row.get("snapshot")
        if not isinstance(snap, str):
            continue
        try:
            data = json.loads(snap)
        except (TypeError, ValueError):
            continue
        new_status = data.get("etatSante") or data.get("health_status")
        new_rank = _rank(new_status)
        if new_rank is not None and new_rank > current:
            return 1
    return 0
