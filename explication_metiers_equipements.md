# 🚜 Guide Technique: Les Métiers Équipements & Garages (Validation)

Mela, pour bien réussir la validation et répondre aux questions techniques du prof, voici une explication claire, simple et technique (avec code à l'appui) de nos 4 fonctionnalités métiers principales, bel arbi tounsi (mixte avec français).

---

## 1. 🤖 Chatbot Équipements (Assistant Agricole)

L'idée heya n3awnou l'utilisateur yefhem l'utilité mta3 les équipements (tracteurs, drones, capteurs) w yelga des réponses fissa fissa.

**📂 Les fichiers concernés :**
- `src/Controller/UserDashboardController.php` (Méthode `chatbotAsk()`)
- `templates/user/equipements/index.html.twig` (Interface Chatbot et JavaScript)

**🛠️ Kifech tekhdem (Les Étapes) :**
1. **Frontend (AJAX) :** L'utilisateur yekteb question. Bel JavaScript (`fetch API`), na3mlou appel AJAX bil méthode `POST` l'route `/user/chatbot/ask` bech neb3thou el message fi format JSON.
2. **Backend (Traitement) :** Fel Controller, njibou l'message, nrodouh lkol *lowercase*, w nebdeou n'testiw bel fonction `str_contains()`. 
   - *Exemple :* `if (str_contains($message, 'tracteur')) { $response = "Tracteur Agricole..."; }` (Rule-based AI).
3. **Simulation Humaine :** Zidna fonction `usleep(500000);` (0.5 secondes) bech n'simuliw wa9t l'ktiba (typing time) bech ybén l'assistant naturel.
4. **Retour (Response) :** Nraj3ou réponse b `$this->json(['reply' => $response])` w l'JavaScript y'affichiha fel bulle de chat.

---

## 2. 📊 Statistiques Chart.js (Disponibilité des Équipements)

Bech n'affichiw un graphique circulaire (Pie Chart) dynamique li ywari les équipements disponibles vs en panne.

**📂 Les fichiers concernés :**
- `src/Controller/UserDashboardController.php` et `AdminController.php`
- Bundle Symfony utilisé : `symfony/ux-chartjs`

**🛠️ Kifech tekhdem (Les Étapes) :**
1. **Calcul des données :** Fel Controller, njibou les équipements lkol mel Repository, na3mlou boucle `foreach` w n7esbou 9adech 3anna min "Disponible", "Indisponible", "En panne", w "En maintenance". N7otou hetha fi tableau PHP `$dispoCount`.
2. **Création du Chart :** Nesta3mlou `ChartBuilderInterface`.
   ```php
   $chartEquipement = $chartBuilder->createChart(Chart::TYPE_PIE);
   $chartEquipement->setData([
       'labels' => array_keys($dispoCount),
       'datasets' => [[
           'backgroundColor' => ['#2ecc71', '#e74c3c', '#e67e22', '#3498db'], // Vert, Rouge, Orange, Bleu
           'data' => array_values($dispoCount),
       ]]
   ]);
   ```
3. **Affichage Twig :** N3adiw l'objet `$chartEquipement` l'template. Fel Twig, on utilise l'objet pour générer le JavaScript directement: 
   `new Chart(ctx, { data: {{ chartEquipement.createView().data|json_encode|raw }} });`

---

## 3. 🔄 Service d'Assignement des Équipements

L'Admin ynajem y'affecti équipement el wa7ed mel les utilisateurs.

**📂 Les fichiers concernés :**
- `src/Controller/AdminController.php` (Méthode `equipementAssign()`)
- `src/Form/AssignEquipementType.php` (Formulaire)

**🛠️ Kifech tekhdem (Les Étapes) :**
1. L'Admin yenzel 3ala bouton "Assigner", yeta7al formulaire `AssignEquipementType` (li fih champ `user` b `EntityType` yjip liste mta3 les utilisateurs).
2. L'action `equipementAssign` t'géri el soumission mta3 l'formulaire. Ken valide (`$form->isValid()`), Symfony met à jour l'objet Equipement automatiquement.
3. N'executiw `$em->flush()` bech n'sauvgardiw l'utilisateur ejdid fel base de données (Relation `ManyToOne`).
4. **Le Plus métier :** Juste ba3d ma n'sauvgardiw l'assignement, n3aytou l'fonction `$stockAlert->checkAndSendAlert()` bech n'thabtou ken e-stock mta3 les équipements disponibles walla critique (puisque na7ina équipement mel stock disponible).

---

## 4. 📧 Mailing Alerte Fin de Stock (Stock Alert Service)

Hetha service complet ykhalina n'b3thou Email l'Admin ken les équipements disponibles wallew chwaya (<= 3).

**📂 Les fichiers concernés :**
- `src/Service/StockAlertService.php` (Le Cœur du métier)

**🛠️ Kifech tekhdem (Les Étapes) :**
1. **Création d'un Service :** 3malna Classe `StockAlertService` w dakhلنا fiha l'injection de dépendances (DI) lel `EquipementRepository` w `MailerInterface` (Composant Symfony Mailer) fel Constructeur.
2. **Le Calcul du Stock :** L'méthode `checkAndSendAlert()` t'récupéri les équipements w ta7seb 9adech men we7ed l'état mte3ou "Disponible".
3. **Condition Critique :** Ken e-nombre l9ineh a9al wala ysewi `ALERT_THRESHOLD` (li 7atineh 3).
4. **L'envoi de l'Email :** 
   ```php
   $email = (new Email())
       ->from('zidisamir993@gmail.com')
       ->to('admin@agrimans.com')
       ->subject('⚠️ Alerte Stock Équipement : Stock Critique !')
       ->html("<h1>Alerte...</h1> Le nombre d'équipements disponibles est tombé à " . $count);
   $this->mailer->send($email);
   ```
5. Ce service est appelé automatiquement m'el `AdminController` fel création, l'édition, l'assignement ou la suppression d'un équipement. Yekhdem en arrière-plan sans bloquer la vue !

---
**💡 Conseil lel soutenance :** 
Ken l'prof ysa9sik "Alech 3malt service lel alerte mail ?", jawbou: 
*"Bech n'respectiw l'architecture MVC w l'principe Single Responsibility. L'controller yelzmou yab9a nthif (Fat Model, Skinny Controller), donc l'logique mta3 l'calcul de stock w l'envoi de mails 7atineha fi un service indépendant li najmou n3aytouleh men n'importe win fel code mte3na."*
