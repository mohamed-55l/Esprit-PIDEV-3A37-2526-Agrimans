# Guide d’implémentation — module élevage (`/waad`) et services associés

Document à partager avec l’équipe : vue d’ensemble des fonctionnalités ajoutées, **où vit le code**, **comment le tester**, et **quelles commandes** utiliser. Les numéros de ligne correspondent au dépôt au moment de la rédaction ; en cas de divergences, recherchez le symbole de route ou le nom de classe dans l’IDE.

---

## 1. Démarrage rapide (nouveau poste)

| Étape | Commande / action |
|--------|-------------------|
| Dépendances PHP | `composer install` |
| Base de données | Configurer `DATABASE_URL` dans `.env` ou **`.env.local`** (recommandé pour les secrets) |
| Migrations | `composer run db:migrate` ou `php bin/console doctrine:migrations:migrate` |
| État des migrations | `composer run db:migrate:status` |
| Annuler la dernière migration | `composer run db:migrate:prev` |
| Serveur local | `symfony server:start` ou `php -S localhost:8000 -t public` |
| Lister les routes du module | `php bin/console debug:router \| findstr waad` (Windows) ou `grep waad` sous Unix |

**Sécurité des clés API** : ne jamais committer de vraies clés. Mettre `OPENROUTER_*`, `OPENWEATHER_*`, `POLLINATIONS_*` dans **`.env.local`** (ignoré par git si présent dans `.gitignore`). Le fichier `new-function.md` à la racine résume les variables sans exposer de secrets.

---

## 2. Bundles Composer concernés

| Bundle | Rôle dans le projet | Config | Code métier typique |
|--------|---------------------|--------|---------------------|
| **knplabs/knp-paginator-bundle** | Pagination liste animaux | `config/packages/knp_paginator.yaml` | `AnimalController::index` |
| **vich/uploader-bundle** | Photo animal sur disque + nom en BDD | `config/packages/vich_uploader.yaml` (mapping `animal_images`) | `Animal` + `AnimalType` |
| **dompdf/dompdf** | Export PDF liste animaux | (utilisé dans le service PHP) | `AnimalListPdfExporter` |
| **symfony/http-client** | Appels OpenRouter / OpenWeather | — | `OpenRouterAnimalInsightService`, `OpenWeatherFarmService` |

Enregistrement des bundles : `config/bundles.php` (lignes typiques **16–17** : `KnpPaginatorBundle`, `VichUploaderBundle`).

---

## 3. Sécurité : accès `/waad`

- **Contrôle d’accès HTTP** : `config/packages/security.yaml` — règle **`path: ^/waad, roles: ROLE_USER`** (vers la ligne **39**).
- **Contrôle applicatif** : attribut `#[IsGranted('ROLE_USER')]` sur les contrôleurs animaux / repas par animal :
  - `src/Controller/AnimalController.php` — classe entière **lignes 30–31**
  - `src/Controller/AnimalNourritureController.php` — **lignes 20–21**

Sans `ROLE_USER`, l’utilisateur est redirigé vers la page de connexion (comportement Symfony Security).

---

## 4. Routes principales (référence)

Préfixe commun animaux : **`/waad/animal`**.

| Nom de route | Méthode | Chemin | Contrôleur / action |
|----------------|---------|--------|---------------------|
| `waad_animal_index` | GET | `/waad/animal` | `AnimalController::index` |
| `waad_animal_new` | POST | `/waad/animal/new` | `AnimalController::new` |
| `waad_animal_show` | GET | `/waad/animal/{id}` | `AnimalController::show` |
| `waad_animal_edit` | POST | `/waad/animal/{id}/edit` | `AnimalController::edit` |
| `waad_animal_delete` | POST | `/waad/animal/{id}/delete` | `AnimalController::delete` |
| `waad_animal_restore` | POST | `/waad/animal/{id}/restore` | `AnimalController::restore` |
| `waad_animal_stats` | GET | `/waad/animal/stats` | `AnimalController::stats` |
| `waad_animal_historique` | GET | `/waad/animal/historique` | `AnimalController::historique` |
| `waad_animal_archive` | GET | `/waad/animal/archive` | `AnimalController::archive` |
| `waad_animal_export_pdf` | GET | `/waad/animal/export/pdf` | `AnimalController::exportPdf` |
| `waad_animal_notifications` | GET | `/waad/animal/notifications` | `AnimalController::notifications` |
| `waad_animal_notifications_poll` | GET | `/waad/animal/notifications/api/poll` | `AnimalController::notificationsPoll` |
| `waad_animal_pollinations` | POST | `/waad/animal/{id}/image-ia` | `AnimalController::generateImage` |
| `waad_feeding_*` | POST | `/waad/animal/{animalId}/feeding/...` | `AnimalNourritureController` |
| `waad_nourriture_*` | GET/POST | `/waad/nourriture` | `NourritureController` |

Vérification : `php bin/console debug:router waad_animal_stats`.

---

## 5. Fichiers clés par fonctionnalité

### 5.1 Contrôleur principal animaux

**Fichier** : `src/Controller/AnimalController.php`

| Zone (lignes approx.) | Rôle |
|------------------------|------|
| **30–39** | Préfixe route `/waad/animal`, `ROLE_USER`, helper `currentUserId()` |
| **41–71** | **Stats** : agrégats repo + météo + IA si `?analyse_ia=1` |
| **73–101** | Historique, archive, export PDF |
| **103–189** | Notifications (liste, poll JSON, marquer lues) |
| **191–223** | **Index + KnpPaginator** (10 par page) |
| **225–254** | Création animal + logger + notifier |
| **256–282** | Restauration depuis archive |
| **284–308** | **Pollinations** : POST image IA, CSRF `animal_pollinations` |
| **310–374** | Fiche animal, édition |
| **376–403** | Archivage (soft delete) |
| **405–429** | `limitGroupedSeries()` pour le top races sur les stats |

### 5.2 Repas liés à un animal

**Fichier** : `src/Controller/AnimalNourritureController.php`  
**Préfixe** : `/waad/animal/{animalId}/feeding` — actions **new / edit / delete** avec journalisation et notifications selon implémentation.

### 5.3 Stock nourriture global (hors fiche animal)

**Fichier** : `src/Controller/NourritureController.php`  
**Préfixe** : `/waad/nourriture` — CRUD entité `Nourriture` (protégé par la règle `^/waad` dans `security.yaml`).

### 5.4 Dépôt de données animaux

**Fichier** : `src/Repository/AnimalRepository.php`

Méthodes utiles pour stats et listes : `createActiveQueryBuilder`, `findOneActiveById`, `findOneArchivedById`, `findAllArchived`, `countActive`, `countArchived`, `countActiveGroupedBySpecies`, `countActiveGroupedByHealth`, `countActiveGroupedByBreed`.

### 5.5 Notifications utilisateur

**Entité** : `src/Entity/UserNotification.php`  
**Dépôt** : `src/Repository/UserNotificationRepository.php` — `countUnreadForUser`, `findForUser`, `findMaxIdForUser`, `findAnimalNotificationsWithIdGreaterThan`, `markAllReadForUser`.

**Service** : `src/Service/AnimalNotifier.php` — centralise la création des lignes `user_notification` sur les événements métier.

### 5.6 Historique des actions

**Entité** : `src/Entity/AnimalHistory.php`  
**Dépôt** : `src/Repository/AnimalHistoryRepository.php`  
**Service** : `src/Service/AnimalActivityLogger.php` — enregistre création / mise à jour / archivage / restauration / repas, etc.

### 5.7 OpenRouter (analyse IA des stats)

**Fichier** : `src/Service/OpenRouterAnimalInsightService.php`  
- Lit **`OPENROUTER_API_KEY`** et **`OPENROUTER_MODEL`** (injection `#[Autowire('%env(...)%')]`).  
- Méthode **`generateInsight(array $stats)`** : envoie le JSON des stats à l’API chat OpenRouter.  
- Appelée depuis **`AnimalController::stats`** lorsque la requête contient **`analyse_ia=1`** (voir **lignes 61–64** du contrôleur).

**Test manuel** : ouvrir `/waad/animal/stats?analyse_ia=1` (utilisateur connecté avec clé API configurée).

### 5.8 OpenWeather (bloc météo sur les stats)

**Fichier** : `src/Service/OpenWeatherFarmService.php`  
- Variables typiques : **`OPENWEATHER_API_KEY`**, **`OPENWEATHER_CITY`**.  
- Utilisé dans **`AnimalController::stats`** (passage de `weather` au template).

### 5.9 Pollinations (URL d’image générée / illustrative)

**Fichier** : `src/Service/PollinationsImageService.php`  
- Construit une URL `https://image.pollinations.ai/prompt/...` ; paramètre optionnel clé **`POLLINATIONS_API_KEY`**.  
- **`AnimalController::generateImage`** (**lignes 284–308**) : POST avec CSRF, enregistre `externalImageUrl` sur l’entité.

### 5.10 Export PDF

**Fichier** : `src/Service/AnimalListPdfExporter.php`  
- Route **`waad_animal_export_pdf`** — **`AnimalController::exportPdf`** (**lignes 89–101**).

### 5.11 Formulaire animal + upload Vich

**Fichier** : `src/Form/AnimalType.php` — champ **`VichImageType`** pour `imageFile` (vers **lignes 36+** selon version).  
**Entité** : `src/Entity/Animal.php` — attributs **`#[Vich\Uploadable]`** et **`#[Vich\UploadableField(mapping: 'animal_images', ...)]`** (zone vers **lignes 15 et 236**).  
**Stockage** : `public/uploads/animals` (voir `config/packages/vich_uploader.yaml`).

### 5.12 Stats + graphiques (Chart.js)

**Template** : `templates/animal/stats.html.twig`  
- KPI (actifs, archives, total), JSON embarqué **`#animal-stats-chart-data`**, inclusion **Chart.js** (CDN) + script **`public/js/animal-stats-charts.js`**.  
- Données produites dans **`AnimalController::stats`** (`par_espece`, `par_sante`, `par_race`, totaux).

### 5.13 Badge « non lues » dans la navbar

**Subscriber** : `src/EventSubscriber/AnimalNotificationTwigSubscriber.php`  
- Sur requête principale : expose la globale Twig **`animal_notif_unread`** (**lignes 38–49**).  
**Fallback** : `config/packages/twig.yaml` — globale **`animal_notif_unread: 0`** (**lignes 4–5**).

**Template** : `templates/base.html.twig` — lien cloche vers les notifications (**lignes ~114–120**), attribut **`data-animal-notif-poll`** sur `<body>` (**ligne ~35**), bouton notifications bureau (**lignes ~114–116**), script **`public/js/animal-desktop-notifications.js`** (**lignes ~145–147**).

### 5.14 Notifications « système » (navigateur / Windows)

**API** : `GET /waad/animal/notifications/api/poll?since=<id>` — **`AnimalController::notificationsPoll`** (**lignes 116–152**).  
**Front** : `public/js/animal-desktop-notifications.js` — `fetch` périodique, `Notification` API, `sessionStorage` pour le dernier `since`.

---

## 6. Migrations liées au module (ordre logique)

Fichiers dans **`migrations/`** (noms exacts selon dépôt) :

| Fichier | Intention (résumé) |
|---------|---------------------|
| `Version20260407231419.php` | Contexte historique / messenger (peut être conditionnel selon environnement) |
| `Version20260421143000.php` | Tables `animal_history`, `user_notification` ; colonnes animal (naissance, soft delete, images, URL externe) |
| `Version20260421180000.php` | Table **`users`** + compte démo optionnel si table vide |
| `Version20260422100000.php` | Alignement `user_id` / FK animal ↔ user |
| `Version20260422120000.php` | Ajustements colonnes `animal` si schéma hybride |

Lire **`getDescription()`** et le corps de **`up()`** de chaque fichier pour le détail SQL exact.

---

## 7. Variables d’environnement (noms à connaître)

| Variable | Usage |
|----------|--------|
| `DATABASE_URL` | Doctrine / MySQL ou autre |
| `OPENROUTER_API_KEY` | IA stats |
| `OPENROUTER_MODEL` | Modèle OpenRouter |
| `OPENWEATHER_API_KEY` | Météo ferme |
| `OPENWEATHER_CITY` | Ville pour la météo |
| `POLLINATIONS_API_KEY` | Optionnel sur l’URL image Pollinations |

Référence courte : **`new-function.md`** à la racine du projet.

---

## 8. Scripts Composer utiles

Définis dans **`composer.json`** (section **`scripts`**) :

- **`composer run db:migrate`** → `doctrine:migrations:migrate --no-interaction`
- **`composer run db:migrate:status`**
- **`composer run db:migrate:prev`**

---

## 9. Tests manuels recommandés (checklist équipe)

1. Connexion utilisateur avec **`ROLE_USER`** (pas seulement admin pour le menu « élevage » complet).
2. **`/waad/animal`** : liste + pagination si > 10 animaux ; création / édition avec **photo** (Vich).
3. **`/waad/animal/stats`** : graphiques ; météo si clé OpenWeather ; **`?analyse_ia=1`** si clé OpenRouter.
4. **`/waad/animal/export/pdf`** : téléchargement PDF.
5. **`/waad/animal/notifications`** : liste ; actions « marquer lue(s) ».
6. Navbar : badge non lues ; clic **icône bureau** → autorisation navigateur → nouvelle notif = toast OS (via navigateur).
7. Fiche animal : **générer image IA** (Pollinations) si le formulaire / bouton est présent dans `templates/animal/show.html.twig`.
8. Archivage + **`/waad/animal/archive`** + restauration.

---

## 10. Schéma entités (lien mental)

- **`Animal`** : cheptel actif (`deletedAt` null) ou archivé ; liaison utilisateur ; champs image Vich + URL image externe.  
- **`AnimalHistory`** : journal des actions.  
- **`UserNotification`** : notifications in-app (et source pour le poll bureau).  
- **`AnimalNourriture`** : repas liés à un animal.  
- **`Nourriture`** : stock / catalogue global sous `/waad/nourriture`.

---

*Fin du guide. Pour toute évolution, mettre à jour ce fichier ou pointer vers des tickets / PR avec résumé des changements.*
