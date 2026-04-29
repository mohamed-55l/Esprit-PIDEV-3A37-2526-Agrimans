# 🗺️ Explication Technique: Map Garage (Leaflet + Météo API)

Bon courage pour ta validation demain ! 💪 Bech t3addi soutenance tayara w tjaweb 3al questions mriguel, hédhi explication technique détaillée 3ala kiféch tkhdem el "Map Garage" mel A lel Z (fichiers, code, wel logique).

---

## 📂 1. Les fichiers concernés (Mnin tebda el khedma ?)
L'intégration mta3 el Map m9asma 3la zouz blayes asasiyin :
1. **Le Controller (Backend) 🧠** : `src/Controller/UserDashboardController.php`
2. **La Vue (Frontend) 🖥️** : `templates/user/equipements/index.html.twig`

---

## ⚙️ 2. Étape 1 : Le Backend (Controller)
Bech t'affichi el Map, lezmek tjib les données mta3 les garages mel base de données. Hédha yssir fel fonction `mesEquipements` :

```php
// src/Controller/UserDashboardController.php

#[Route('/user/equipements', name: 'user_equipement_index')]
public function mesEquipements(GarageRepository $garageRepository): Response
{
    // ... code lekher mta3 les equipements ...

    // Houni njibou les garages lkol mel base de données bech nabaathouhom lel Vue
    $garages = $garageRepository->findAll();

    return $this->render('user/equipements/index.html.twig', [
        // ...
        'garages' => $garages, // Nabaathou tableau mta3 les garages
    ]);
}
```
**🗣️ Kifech t'fassarha fel soutenance :** 
> "Côté backend, j'utilise le `GarageRepository` pour récupérer toutes les entités Garage via la méthode `findAll()`. Ensuite, je passe cette collection à mon template Twig pour pouvoir extraire les coordonnées (latitude/longitude)."

---

## 🍃 3. Étape 2 : Le Frontend (L'affichage mta3 el Map)
Fel fichier Twig `templates/user/equipements/index.html.twig`, nesta3mlou bibliothèque javascript esmha **Leaflet.js** (hiye open-source w t3aweth Google Maps).

### A. Les imports (Importation mta3 Leaflet)
Fel fou9 mta3 l'html, n'importiw les fichiers CSS w JS mta3 Leaflet :
```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
```

### B. Le conteneur HTML
N7ottou div fargha 3andha `id="garagesMap"` hédhi elli bech yetrsem fiha el map :
```html
<div id="garagesMap" style="height: 400px; width: 100%;"></div>
```

### C. L'initialisation (Fel balise `<script>`)
Houni nabdew nktbou fel JS bech n'affichiou el map :
```javascript
// Centre par défaut (ex: Tunis avec latitude 36.8065 et longitude 10.1815) w zoom = 7
var map = L.map('garagesMap').setView([36.8065, 10.1815], 7);

// Ajout mta3 la couche (TileLayer) d'OpenStreetMap (el tsawer mta3 l'khrita)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);
```

---

## 📍 4. Étape 3 : Placement des Marqueurs (Les Garages)
Taw lezemna n'affichiou les garages elli jeyin mel Controller (mab3outhin b Twig) 3al khrita. 

```javascript
// 1. Nkhabbiw les données mta3 les garages fi tableau JS
var garages = [
    {% for garage in garages %}
        {% if garage.latitude and garage.longitude %}
            {
                id: {{ garage.id }},
                nom: "{{ garage.nom|escape('js') }}",
                lat: {{ garage.latitude|number_format(6, '.', '') }},
                lng: {{ garage.longitude|number_format(6, '.', '') }}
            },
        {% endif %}
    {% endfor %}
];

// 2. Na3mlou boucle 3la kol garage bech n7ottou marker (Point)
garages.forEach(function(garage) {
    // Nsobou el point 3al map
    var marker = L.marker([garage.lat, garage.lng]).addTo(map);
    
    // N7adrou el contenu mta3 el Popup (wa9telli teklicki 3al point)
    var popupContent = `<h4>${garage.nom}</h4><div id="weather-garage-${garage.id}"></div>`;
    marker.bindPopup(popupContent);
});
```

---

## ⛅️ 5. Le Bonus Technique : L'API Météo en Temps Réel (The WOW Factor 🤩)
Hedhi a9wa phase fel map, elli hia tjib el météo mta3 e'lblassa win mawjoud el garage. Nesta3mlou fi API esmha **Open-Meteo**.

Wakt'elli l'utilisateur yklicki 3al marqueur (événement `popupopen`), nabaathou requete AJAX (`fetch`) bech njibou el météo :

```javascript
marker.on('popupopen', async function(e) {
    var popup = e.popup;
    if (popup._weatherFetched) return; // Ken jbnaha 9bal, ma nzidouch njibouha (Optimisation)

    try {
        // N'abaathou requete lel API m3a lat wel lng mta3 el garage
        const response = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${garage.lat}&longitude=${garage.lng}&current_weather=true`);
        const data = await response.json();
        const weather = data.current_weather;
        
        // Logique mta3 alertes (Ken famma mtar, skhana, thalj...)
        let impactHtml = '';
        if (weather.temperature > 35) {
            impactHtml = '🔥 Chaleur extrême : Risque de surchauffe.';
        } else if (weather.weathercode >= 61 && weather.weathercode <= 65) {
            impactHtml = '⚠️ Pluie : Risque d\'oxydation.';
        }
        
        // N'affichiouha fel popup
        document.getElementById('weather-garage-' + garage.id).innerHTML = `${weather.temperature}°C - ${impactHtml}`;
        popup._weatherFetched = true;
    } catch (err) {
        // Gestion des erreurs
    }
});
```
**🗣️ Kifech t'fassarha :** 
> "Pour ajouter de la valeur, j'ai intégré une API externe (Open-Meteo). Au lieu de charger la météo de tous les garages au chargement de la page (ce qui serait lourd), j'utilise un système **Asynchrone (AJAX)**. L'appel à l'API ne se fait que lorsque l'utilisateur clique sur le marqueur (`popupopen`). En plus, j'ai implémenté une logique d'alerte métier : selon le code météo, je préviens l'utilisateur des risques pour son équipement agricole."

---

## 📝 Résumé Rapide (Bech t'révisih fel thneya)
1. **Backend** : `findAll()` mel `GarageRepository` -> Twig.
2. **Frontend** : **Leaflet.js** t'affichi el Map (OpenStreetMap).
3. **Marqueurs** : Boucle Twig ta9ra les `latitude` w `longitude` w t'placi les `L.marker`.
4. **Météo AJAX** : API Open-Meteo 3al click, t'affichi el température w ta3ti des conseils lel agriculteur selon l'état météo.

**Rabi m3ak fel validation, l'approche hédhi fiha map interactive + API externe (Météo) + Javascript Asynchrone, tawa tsakker biha ay question technique ! 🚀**
