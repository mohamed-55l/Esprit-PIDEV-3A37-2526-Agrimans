"""Read animal-module data from the project MySQL database."""
from __future__ import annotations

import os
from pathlib import Path

import pandas as pd
import pymysql
from dotenv import load_dotenv

ROOT = Path(__file__).resolve().parent.parent
load_dotenv(ROOT / ".env")


def connect() -> pymysql.connections.Connection:
    return pymysql.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", "3306")),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASS", ""),
        database=os.getenv("DB_NAME", "agrimans"),
        charset="utf8mb4",
        autocommit=True,
    )


def fetch_animals() -> pd.DataFrame:
    """All animals (active + archived). Uses canonical columns; falls back to FR aliases."""
    sql = """
        SELECT
            id,
            COALESCE(name, nom)                AS name,
            COALESCE(type, espece)             AS species,
            COALESCE(breed, race)              AS breed,
            COALESCE(weight, poids)            AS weight,
            COALESCE(health_status, etatSante) AS health_status,
            date_naissance,
            deleted_at,
            user_id
        FROM animal
    """
    with connect() as cnx:
        df = pd.read_sql(sql, cnx)
    for col in ("date_naissance", "deleted_at"):
        df[col] = pd.to_datetime(df[col], errors="coerce")
    return df


def fetch_feedings() -> pd.DataFrame:
    sql = """
        SELECT animal_id, quantity_fed, feeding_date
        FROM animal_nourriture
        WHERE feeding_date IS NOT NULL
    """
    with connect() as cnx:
        df = pd.read_sql(sql, cnx)
    df["feeding_date"] = pd.to_datetime(df["feeding_date"], errors="coerce")
    df = df.dropna(subset=["feeding_date"])
    return df


def fetch_history() -> pd.DataFrame:
    """History rows: action + created_at, used to derive labels (status changes)."""
    sql = """
        SELECT animal_id, action, snapshot, created_at
        FROM animal_history
    """
    with connect() as cnx:
        df = pd.read_sql(sql, cnx)
    df["created_at"] = pd.to_datetime(df["created_at"], errors="coerce")
    return df
