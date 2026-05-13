-- Script pour insérer les équipements de démonstration (Matching Intelligent)
-- À exécuter pour préparer la soutenance

-- Petit Tracteur (capacité 0.5 Ha/heure)
INSERT INTO equipement (nom, type, prix, disponibilite, capacite_rendement) 
VALUES 
    ('Petit Tracteur', 'Tracteur', 15000, 'Disponible', 0.5),
    ('Moissonneuse Lourde', 'Moissonneuse', 45000, 'Disponible', 5.0),
    ('Tracteur Moyen', 'Tracteur', 25000, 'Disponible', 2.5);

-- Optionnel : Insérer une parcelle de test de 20 Ha
-- INSERT INTO parcelle (nom, superficie, localisation, type_sol) 
-- VALUES ('Champ de Démonstration', 20, 'Ariana', 'Terre noire');
