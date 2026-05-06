# Animal Health Risk Model (Python · River)

A small online-learning model that predicts which animals are likely to suffer
a health degradation in the next 30 days, based on their feeding history,
weight, age, and species.

The model is **incremental** — every run keeps learning. The trained pipeline
is persisted to `var/animal_health_model.pkl`, so each new feeding/health
record makes the model a bit smarter without retraining from scratch.

## 1. Install

```bash
cd python
python -m venv .venv
# Windows: .venv\Scripts\activate     |     Linux/Mac: source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env       # adjust DB_USER / DB_PASS if needed
```

## 2. Train (online — keeps existing knowledge)

```bash
python -m animal_health.train
```

Add `--reset` to start from a fresh model.
Add `--step-days 14 --horizon-days 21` to change the observation cadence and
look-ahead window.

Output:
```
Loaded 12 animals, 87 feedings, 34 history rows.
Trained on 145 samples (18 positives).
Running accuracy: Accuracy: 87.59%
Running F1:       F1: 0.612
Model saved to var/animal_health_model.pkl
```

## 3. Predict for active animals

```bash
python -m animal_health.predict --top 10
```

Writes `var/predictions.json`:

```json
[
  {
    "animal_id": 7,
    "name": "Bella",
    "species": "Vache",
    "current_health": "Bonne",
    "risk_score": 0.78,
    "risk_label": "high",
    "predicted_at": "2026-05-06T14:23:11"
  }
]
```

The Symfony app can read this file (`HttpKernel`-side) or a controller can
expose `/animal/at-risk` that simply `json_decode`s it.

## 4. How it learns

| Step | What happens |
|------|--------------|
| `train.py` walks each animal's timeline week by week. |
| For each (animal, date) pair it builds a feature dict. |
| The label is `1` if the animal got archived **or** its health rank worsened in the next 30 days. |
| `model.learn_one(features, label)` updates the River pipeline (StandardScaler → LogisticRegression). |
| Pipeline pickled to disk. |

Re-run `train` whenever new history appears — the model just gets better.

## 5. Project layout

```
python/
├── animal_health/
│   ├── db.py          # MySQL queries (animals, feedings, history)
│   ├── features.py    # per-animal feature dict at instant T
│   ├── labels.py      # binary label from future health/archive events
│   ├── model.py       # River pipeline + load/save
│   ├── train.py       # CLI: incremental training
│   └── predict.py     # CLI: writes var/predictions.json
├── var/               # generated: model + predictions
├── requirements.txt
├── .env.example
└── README.md
```

## 6. Suggested integration with Symfony

1. **Cron** (Windows: Task Scheduler) — every night:
   ```
   python -m animal_health.train
   python -m animal_health.predict --top 50
   ```
2. **Symfony controller** reads `python/var/predictions.json` and exposes
   `/waad/animal/at-risk` showing a sortable list of risky animals.
3. **Twig badge** on the animals index — red dot next to animals whose
   `risk_label === "high"`.
