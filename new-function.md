# Extensions prévues (référence)

## OpenRouter (analyse IA du tableau de bord)

- Endpoint: `https://openrouter.ai/api/v1/chat/completions`
- Variables: `OPENROUTER_API_KEY`, `OPENROUTER_MODEL` (voir `.env`)
- Ne jamais committer de clé API ; utiliser `.env.local`.

## Statistiques & historique

- Tableau de bord élevage: `/waad/animal/stats` (météo + bouton analyse IA)
- Historique / archivage: `/waad/animal/historique`, `/waad/animal/archive`
- Suppression logique des animaux (archivage) avec restauration possible

## Notifications

- Stockées en base (`user_notification`), badge dans la barre de navigation
- Créées sur les opérations CRUD animaux et sur le suivi des repas

## PDF

- Liste des animaux actifs: `/waad/animal/export/pdf` (dompdf)

## Images (Pollinations)

- Documentation: [Pollinations image API](https://enter.pollinations.ai/api/docs)
- Variable optionnelle: `POLLINATIONS_API_KEY`

## Bundles

- `vich/uploader-bundle` — photos animaux
- `knplabs/knp-paginator-bundle` — pagination liste animaux

## Météo (OpenWeather)

- Variable: `OPENWEATHER_API_KEY`, `OPENWEATHER_CITY` (voir `.env`)
