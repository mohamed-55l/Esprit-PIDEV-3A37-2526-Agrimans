# Cloner le projet et lancer l’application (nouveau poste)

Guide pour un coéquipier après `git clone` (branche **`main`** ou **`animals-management`** selon ce que l’équipe suit).

---

## 1. Prérequis sur la machine

| Outil | Version / remarque |
|--------|-------------------|
| **PHP** | ≥ **8.1** (extensions courantes Symfony : `ctype`, `iconv`, `json`, `mbstring`, `pdo_mysql`, `xml`, `tokenizer`, …) |
| **Composer** | 2.x ([getcomposer.org](https://getcomposer.org/)) |
| **MySQL ou MariaDB** | Serveur local accessible (ex. port **3306**) |
| (Optionnel) **Symfony CLI** | [symfony.com/download](https://symfony.com/download) — pratique pour `symfony server:start` |

---

## 2. Cloner le dépôt

```bash
git clone https://github.com/mohamed-55l/Esprit-PIDEV-3A37-2526-Agrimans.git
cd Esprit-PIDEV-3A37-2526-Agrimans
```

Pour travailler sur la branche module élevage :

```bash
git fetch origin
git checkout animals-management
```

---

## 3. Installer les dépendances PHP

```bash
composer install
```

À la fin, Flex exécute en général `cache:clear` et `assets:install` automatiquement.

---

## 4. Base de données

1. Créer une base vide (exemple MySQL) :

   ```sql
   CREATE DATABASE agrimans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Configurer la connexion :
   - Le fichier **`.env`** du dépôt contient souvent une URL du type  
     `mysql://root:@127.0.0.1:3306/agrimans`  
   - Adapter **utilisateur**, **mot de passe** et **nom de base** à votre poste.
   - **Recommandé** : créer **`.env.local`** (non versionné) et y mettre uniquement :

     ```env
     DATABASE_URL="mysql://USER:PASSWORD@127.0.0.1:3306/agrimans?serverVersion=8.0&charset=utf8mb4"
     ```

     Remplacer `USER`, `PASSWORD` et ajuster `serverVersion` si besoin (MariaDB / autre version).

3. Appliquer le schéma avec les migrations :

   ```bash
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

   Ou : `composer run db:migrate`

---

## 5. Lancer le serveur web

**Option A — Symfony CLI (recommandé en dev)**

```bash
symfony server:start
```

Ouvrir l’URL affichée (souvent `https://127.0.0.1:8000`).

**Option B — Serveur PHP intégré**

```bash
php -S 127.0.0.1:8000 -t public
```

Puis ouvrir : **http://127.0.0.1:8000**

---

## 6. Se connecter à l’application

La migration **`Version20260421180000`** insère un **compte démo** seulement si la table **`users`** est vide au moment de la migration :

| Champ | Valeur |
|--------|--------|
| Email | `demo@agrimans.local` |
| Mot de passe | `agrimans123` |

Si la table `users` n’était pas vide lors de la migration, ce compte n’existe pas : créer un utilisateur via les écrans d’inscription / admin selon ce qui est prévu dans le projet, ou vider la table puis relancer la migration concernée (à faire seulement en dev, avec accord d’équipe).

Le rôle en base est `USER` ; l’entité mappe cela vers **`ROLE_USER`** pour accéder aux routes sous **`/waad`**.

---

## 7. Fonctions optionnelles (clés API)

Sans configuration, certaines parties tournent en mode dégradé (message dans l’UI ou texte de repli) :

| Besoin | Variables (dans **`.env.local`**, pas dans git) |
|--------|---------------------------------------------------|
| Analyse IA des stats | `OPENROUTER_API_KEY`, `OPENROUTER_MODEL` |
| Météo sur les stats | `OPENWEATHER_API_KEY`, `OPENWEATHER_CITY` |
| Image Pollinations (paramètre d’URL) | `POLLINATIONS_API_KEY` (optionnel) |

Voir aussi **`new-function.md`** à la racine du dépôt.

---

## 8. En cas de problème

| Symptôme | Piste |
|----------|--------|
| Erreur de connexion DB | Vérifier `DATABASE_URL`, que MySQL tourne, que la base existe. |
| Erreur « table manquante » | Relancer `doctrine:migrations:migrate`. |
| Page blanche / 500 | Regarder **`var/log/dev.log`** avec `APP_ENV=dev`. |
| Assets / images | `php bin/console assets:install public` |

Documentation détaillée du module élevage : **`docs/GUIDE_IMPLEMENTATION_EQUIPE_MODULE_WAAD.md`**.

---

*Dernière mise à jour : guide de premier lancement post-clone.*
