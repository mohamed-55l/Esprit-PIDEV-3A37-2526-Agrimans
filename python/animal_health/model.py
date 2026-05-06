"""River pipeline + persistence on disk. The model keeps learning across runs."""
from __future__ import annotations

import pickle
from pathlib import Path
from typing import Any

from river import compose, linear_model, preprocessing

ROOT = Path(__file__).resolve().parent.parent
MODEL_PATH = ROOT / "var" / "animal_health_model.pkl"


def new_model() -> Any:
    return compose.Pipeline(
        ("scale", preprocessing.StandardScaler()),
        ("lr", linear_model.LogisticRegression()),
    )


def load_model() -> Any:
    if MODEL_PATH.exists():
        with MODEL_PATH.open("rb") as f:
            return pickle.load(f)
    return new_model()


def save_model(model: Any) -> None:
    MODEL_PATH.parent.mkdir(parents=True, exist_ok=True)
    with MODEL_PATH.open("wb") as f:
        pickle.dump(model, f)
