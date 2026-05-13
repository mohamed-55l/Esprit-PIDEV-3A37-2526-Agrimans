# 🎯 Guide Accès Rapide - Démonstration Pratique

## 📍 Comment Accéder à la Démo ?

### Option 1 : Via la Navbar (Recommandé)
```
Connexion → Ressources Agro → Matching Intelligent ⭐
```

### Option 2 : URL Directe
```
http://localhost:8000/equipement/demo
```

### Option 3 : Via la Page des Équipements
```
Équipements → [Bouton Démonstration] → Cliquer
```

---

## 🎬 Flux d'Utilisation (Étapes)

### 1️⃣ Charger la Page de Démo
Accédez à `/equipement/demo`

Vous verrez :
- **Sidebar gauche** : Liste des parcelles disponibles
- **Zone principale** : Message "Sélectionnez une parcelle à gauche"

### 2️⃣ Sélectionner une Parcelle
Cliquez sur une parcelle dans la sidebar

**Exemple :**
```
👉 "Champ de Démonstration (20 Ha)"
```

**Résultat :**
- La parcelle s'active (badge bleu)
- Les recommandations s'affichent automatiquement (AJAX)
- Affichage des infos : nom, localisation, surface

### 3️⃣ Visualiser les Recommandations (TOP 3)

La page affiche 3 cards côte à côte :

**Card 1 : 🥇 (Top 1)**
- Badge vert : **RECOMMANDÉ** (100%)
- Exemple : Moissonneuse Lourde
- Temps : 4 heures
- Bouton "🔍 Analyser"

**Card 2 : 🥈 (Top 2)**
- Badge jaune : **ACCEPTABLE** (80%)
- Exemple : Tracteur Moyen
- Temps : 8 heures
- Bouton "🔍 Analyser"

**Card 3 : 🥉 (Top 3)**
- Badge rouge : **NON RECOMMANDÉ** (20%)
- Exemple : Petit Tracteur
- Temps : 40 heures
- Bouton "🔍 Analyser"

### 4️⃣ Cliquer sur "Analyser" pour Voir le Détail

**Pour la Moissonneuse (Recommandée) :**
```
Cliquer sur "🔍 Analyser"

Affichage :
- Score : 100% (badge vert)
- Calcul : 20 Ha ÷ 5 Ha/h = 4 heures
- Raison : "Excellent match ! 4 heures avec rendement optimal."
- Statut : ✓ RECOMMANDÉ
```

**Pour le Petit Tracteur (Non Recommandé) :**
```
Cliquer sur "🔍 Analyser"

Affichage :
- Score : 20% (badge rouge)
- Calcul : 20 Ha ÷ 0.5 Ha/h = 40 heures
- Raison : "Pas recommandé. Machine sous-dimensionnée. Travail en 40.0h."
- Alerte : "🔴 Machine insuffisante : temps trop long, faible productivité."
- Statut : ✗ NON RECOMMANDÉ
```

---

## 📊 Données de Test à Préparer

Avant de présenter, insérez ces données SQL :

```sql
-- Équipements
INSERT INTO equipement (nom, type, prix, disponibilite, capacite_rendement) 
VALUES 
    ('Petit Tracteur', 'Tracteur', 15000, 'Disponible', 0.5),
    ('Moissonneuse Lourde', 'Moissonneuse', 45000, 'Disponible', 5.0),
    ('Tracteur Moyen', 'Tracteur', 25000, 'Disponible', 2.5);

-- Parcelle
INSERT INTO parcelle (nom, superficie, localisation, type_sol) 
VALUES ('Champ de Démonstration', 20, 'Ariana', 'Terre noire');
```

Ou exécutez le fichier prêt à l'emploi :
```bash
mysql -u root -p agrimans < demo_equipement_data.sql
```

---

## 🖱️ Scénario Complet (3-5 minutes)

### Setup Initial (30 secondes)
1. Charger les données SQL
2. Accéder à `/equipement/demo`
3. Vérifier que les parcelles et équipements s'affichent

### Démonstration (2-3 minutes)
1. **[Cliquer]** sur "Champ de Démonstration (20 Ha)"
2. **[Observer]** les 3 recommandations qui s'affichent instantanément
3. **[Lire]** les scores, les badges de couleur
4. **[Cliquer]** sur "Analyser" pour le Petit Tracteur (rouge)
5. **[Narrer]** : "Regardez le score de 20%. Cet équipement prend 40 heures. C'est inefficace."
6. **[Cliquer]** sur "Analyser" pour la Moissonneuse (vert)
7. **[Narrer]** : "Maintenant la Moissonneuse : 100% en vert, 4 heures, c'est optimal."

### Conclusion (30 secondes)
> "Mon système assiste l'agriculteur dans sa décision économique en lui affichant clairement quel équipement utiliser."

---

## 🎨 Éléments Visuels Importants

### Système de Couleurs
- 🟢 **Vert** = RECOMMANDÉ (score ≥ 85%)
- 🟡 **Jaune** = ACCEPTABLE (score 65-84%)
- 🔴 **Rouge** = NON RECOMMANDÉ (score < 65%)

### Badges
- **100%** = Match parfait
- **80%** = Acceptable
- **20%** = Mauvais match

### Medalles
- 🥇 = Meilleur choix
- 🥈 = Deuxième option
- 🥉 = Troisième option

---

## 🔧 Troubleshooting

### Les parcelles ne s'affichent pas
**Cause :** Pas de parcelles en base de données
**Solution :** Créez une parcelle via `/parcelle/new` ou exécutez le SQL

### Les boutons "Analyser" ne répondent pas
**Cause :** Erreur AJAX / Erreur de route
**Solution :** 
```bash
# Vérifier les routes
php bin/console debug:router | grep app_equipement_ajax
```

### Score incorrect ou inexistant
**Cause :** Équipement sans `capacite_rendement`
**Solution :** Éditez l'équipement et renseignez la capacité en Ha/h

### Page toute blanche
**Cause :** Erreur PHP / Template Twig
**Solution :**
```bash
# Vérifier les logs
tail -f var/log/dev.log
```

---

## 📝 Notes pour la Présentation

1. **Commencez par l'accueil** : Montrez la navbar avec le lien "Matching Intelligent"
2. **Cliquez sur la démo** : Entrez dans la page `/equipement/demo`
3. **Sélectionnez une parcelle** : Cliquez sur "Champ de Démonstration"
4. **Présentez les recommandations** : "Voici les 3 meilleures options"
5. **Analysez les mauvais choix** : Montrez pourquoi c'est rouge
6. **Analysez les bons choix** : Montrez pourquoi c'est vert
7. **Concluez** : Expliquez la valeur métier

---

## 🚀 URLs Rapides

| Description | URL |
|-------------|-----|
| Page de démo | `/equipement/demo` |
| API recommandations | `/equipement/api/recommend/1` |
| API matching détail | `/equipement/api/match/1/1` |
| Page équipements | `/equipement` |
| Créer équipement | `/equipement/new` |

---

## ✅ Checklist Avant Présentation

- [ ] Base de données a les équipements de test (0.5, 2.5, 5 Ha/h)
- [ ] Au moins une parcelle de 20 Ha existe
- [ ] Page `/equipement/demo` charge sans erreur
- [ ] Cliquer sur une parcelle affiche les recommandations
- [ ] Cliquer sur "Analyser" montre le détail du matching
- [ ] Les couleurs (vert/jaune/rouge) s'affichent correctement
- [ ] Les calculs sont visibles (Surface / Capacité = Temps)
- [ ] Lien dans la navbar fonctionne ("Matching Intelligent ⭐")

---

**Vous êtes prêt pour la soutenance ! 🎓**
