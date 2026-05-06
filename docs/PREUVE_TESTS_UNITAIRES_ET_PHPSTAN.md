# Preuve — Tests unitaires & analyse statique PHPStan
**Module :** Animaux (Animal, Nourriture, AnimalNourriture)
**Projet :** Agrimans (Symfony 6.4)
**Date :** 2026-05-06
**Auteur :** jizel ziadi
**Branche :** `animals-management`

---

## 1. Objectif

Vérifier la qualité du code du module **Animaux** par deux moyens complémentaires :

1. **Tests unitaires (PHPUnit)** — valider le comportement des entités et de leurs invariants métier.
2. **Analyse statique (PHPStan)** — détecter les bugs typés sans exécuter le code.

---

## 2. Outils installés

| Outil                       | Version  | Rôle                                       |
|-----------------------------|----------|--------------------------------------------|
| `phpunit/phpunit`           | 9.6.34   | Exécution des tests unitaires              |
| `phpstan/phpstan`           | ^2.1     | Analyse statique de typage                 |
| `phpstan/phpstan-symfony`   | ^2.0     | Extension PHPStan pour le conteneur DI     |
| `phpstan/phpstan-doctrine`  | ^2.0     | Extension PHPStan pour les entités ORM     |

Configuration PHPStan : `phpstan.neon.dist` — niveau **5**, scope `src/`.

---

## 3. Tests unitaires — `tests/Entity/`

### 3.1 Fichiers créés

| Fichier                                     | Tests | Cible                                        |
|---------------------------------------------|-------|----------------------------------------------|
| `tests/Entity/AnimalTest.php`               | 10    | `App\Entity\Animal`                          |
| `tests/Entity/NourritureTest.php`           | 7     | `App\Entity\Nourriture`                      |
| `tests/Entity/AnimalNourritureTest.php`     | 3     | `App\Entity\AnimalNourriture` (lien N–N)     |
| **Total**                                   | **20**|                                              |

### 3.2 Commande exécutée

```bash
vendor/bin/phpunit tests/Entity --testdox
```

### 3.3 Résultat — `OK (20 tests, 40 assertions)`

```
PHPUnit 9.6.34 by Sebastian Bergmann and contributors.

Testing tests/Entity

Animal Nourriture (App\Tests\Entity\AnimalNourriture)
 ✔ Feeding record links animal and nourriture
 ✔ Feeding date is recorded
 ✔ Bidirectional registration

Animal (App\Tests\Entity\Animal)
 ✔ Create animal with core fields
 ✔ French and english api are aliases
 ✔ New animal is not archived
 ✔ Soft delete marks archived
 ✔ Restore from archive
 ✔ Animal nourritures collection starts empty
 ✔ Add animal nourriture is idempotent
 ✔ Remove animal nourriture
 ✔ Date naissance is immutable
 ✔ User id ownership

Nourriture (App\Tests\Entity\Nourriture)
 ✔ Core fields
 ✔ Cost is stored as string decimal
 ✔ Cost keeps exact precision
 ✔ Cost is nullable
 ✔ Cost can represent large amount
 ✔ Expiry date
 ✔ Nutritional value is optional

Time: 00:00.031, Memory: 10.00 MB

OK (20 tests, 40 assertions)
```

### 3.4 Couverture fonctionnelle

| Comportement testé                                             | Test                                                |
|----------------------------------------------------------------|-----------------------------------------------------|
| Création d’un animal et accès aux champs principaux            | `testCreateAnimalWithCoreFields`                    |
| Cohérence de l’API bilingue FR / EN                            | `testFrenchAndEnglishApiAreAliases`                 |
| Soft-delete via `deletedAt` + restauration                     | `testSoftDeleteMarksArchived`, `testRestoreFromArchive` |
| Idempotence de `addAnimalNourriture` (pas de doublons)         | `testAddAnimalNourritureIsIdempotent`               |
| Retrait d’une liaison alimentation                             | `testRemoveAnimalNourriture`                        |
| `cost` stocké en `string` (DECIMAL — règle anti-float pour money) | `testCostIsStoredAsStringDecimal`                |
| Précision décimale conservée (`'0.10'` ≠ float `0.1`)          | `testCostKeepsExactPrecision`                       |
| `cost` peut représenter un grand montant sans perte            | `testCostCanRepresentLargeAmount`                   |
| Liaison N–N Animal ↔ Nourriture via `AnimalNourriture`         | `testFeedingRecordLinksAnimalAndNourriture`         |
| Enregistrement bidirectionnel                                  | `testBidirectionalRegistration`                     |

---

## 4. Analyse statique PHPStan

### 4.1 Configuration `phpstan.neon.dist`

```yaml
includes:
    - vendor/phpstan/phpstan-symfony/extension.neon
    - vendor/phpstan/phpstan-doctrine/extension.neon

parameters:
    level: 5
    paths:
        - src
    excludePaths:
        - src/Kernel.php
    symfony:
        containerXmlPath: var/cache/dev/App_KernelDevDebugContainer.xml
```

### 4.2 Commande exécutée — focus module Animaux

```bash
vendor/bin/phpstan analyse \
  src/Entity/Animal.php \
  src/Entity/Nourriture.php \
  src/Entity/AnimalNourriture.php \
  src/Repository/AnimalRepository.php \
  src/Repository/AnimalNourritureRepository.php \
  src/Repository/NourritureRepository.php \
  --level=5
```

### 4.3 Résultat brut — 7 erreurs détectées

```
 ------ -------------------------------------------------------------- 
  Line   Entity/Animal.php
 ------ -------------------------------------------------------------- 
  279    Instanceof between Collection and Collection will always
         evaluate to true.    🪪 instanceof.alwaysTrue
 ------ -------------------------------------------------------------- 

 ------ -------------------------------------------------------------- 
  Line   Entity/AnimalNourriture.php
 ------ -------------------------------------------------------------- 
  106    Method getQuantityFed() should return string|null but
         returns float|null.       🪪 return.type
  111    Property $quantity_fed (float|null) does not accept string.
         🪪 assign.propertyType
  118    Method getFeedingDate() should return DateTime|null but
         returns DateTimeInterface|null.   🪪 return.type
 ------ -------------------------------------------------------------- 

 ------ -------------------------------------------------------------- 
  Line   Entity/Nourriture.php
 ------ -------------------------------------------------------------- 
  185    Instanceof between Collection and Collection will always
         evaluate to true.    🪪 instanceof.alwaysTrue
  219    Method getExpiryDate() should return DateTime|null but
         returns DateTimeInterface|null.   🪪 return.type
  231    Method getDateAdded() should return DateTime|null but
         returns DateTimeInterface|null.   🪪 return.type
 ------ -------------------------------------------------------------- 

 [ERROR] Found 7 errors
```

### 4.4 Synthèse des défauts détectés

| Type d’erreur                | Fichier(s) concerné(s)                     | Impact     |
|------------------------------|--------------------------------------------|------------|
| `instanceof.alwaysTrue`      | `Animal.php:279`, `Nourriture.php:185`     | Code mort  |
| `return.type`                | `AnimalNourriture.php:106`, `:118` ; `Nourriture.php:219`, `:231` | Contrat de retour incorrect (DateTime vs DateTimeInterface) |
| `assign.propertyType`        | `AnimalNourriture.php:111`                 | Mismatch float / string sur `quantity_fed` |

> Ces erreurs préexistaient à l’ajout des tests ; elles seront corrigées lors d’une passe de nettoyage de typage (suppression des `instanceof` redondants, alignement des types de retour `DateTimeInterface` partout).

---

## 5. Reproductibilité

```bash
# Installation (une seule fois)
composer install

# Tests unitaires
vendor/bin/phpunit tests/Entity --testdox

# Analyse statique du module Animaux
vendor/bin/phpstan analyse src/Entity/Animal.php src/Entity/Nourriture.php \
  src/Entity/AnimalNourriture.php src/Repository/AnimalRepository.php \
  src/Repository/AnimalNourritureRepository.php src/Repository/NourritureRepository.php \
  --level=5
```

---

## 6. Conclusion

| Indicateur                              | Valeur     |
|-----------------------------------------|------------|
| Tests unitaires (PHPUnit)               | **20 / 20 OK** |
| Assertions vérifiées                    | **40**     |
| Niveau PHPStan ciblé                    | **5**      |
| Erreurs PHPStan — module Animaux        | 7 (typage) |
| Régression introduite par les changements `float → decimal` (Doctrine Doctor) | **Aucune** |

Le module **Animaux** est couvert par une suite de tests unitaires verte, et l’analyse statique a permis d’identifier précisément les défauts de typage restants — fournissant ainsi la preuve d’une démarche qualité reproductible.
