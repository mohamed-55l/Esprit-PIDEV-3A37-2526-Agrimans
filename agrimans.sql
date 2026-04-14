-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 14 avr. 2026 à 11:16
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `agrimans`
--

-- --------------------------------------------------------

--
-- Structure de la table `animal`
--

CREATE TABLE `animal` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `espece` varchar(100) NOT NULL,
  `race` varchar(100) DEFAULT NULL,
  `poids` float DEFAULT NULL,
  `etatSante` varchar(50) DEFAULT 'Sain',
  `userId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `animal`
--

INSERT INTO `animal` (`id`, `nom`, `espece`, `race`, `poids`, `etatSante`, `userId`) VALUES
(1, 'hj', 'hbjn', 'hbji', 7, 'Gestation', 4),
(2, 'aaa', 'aaa', 'aaa', 777, 'Sain', 2);

-- --------------------------------------------------------

--
-- Structure de la table `animal_nourriture`
--

CREATE TABLE `animal_nourriture` (
  `id` int(11) NOT NULL,
  `quantity_fed` decimal(10,2) NOT NULL,
  `feeding_date` datetime DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `animal_id` int(11) NOT NULL,
  `nourriture_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `animal_nourriture`
--

INSERT INTO `animal_nourriture` (`id`, `quantity_fed`, `feeding_date`, `notes`, `animal_id`, `nourriture_id`) VALUES
(1, 2.00, '2026-04-07 15:30:00', 'dqdf', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `carts`
--

INSERT INTO `carts` (`id`, `buyer_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `culture`
--

CREATE TABLE `culture` (
  `id_culture` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `type_culture` varchar(100) DEFAULT NULL,
  `date_plantation` date DEFAULT NULL,
  `date_recolte_prevue` date DEFAULT NULL,
  `etat_culture` varchar(100) DEFAULT NULL,
  `parcelle_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `culture`
--

INSERT INTO `culture` (`id_culture`, `nom`, `type_culture`, `date_plantation`, `date_recolte_prevue`, `etat_culture`, `parcelle_id`) VALUES
(1, 'AZIZ', 'fff', '2026-04-08', '2026-04-10', 'crouii', 6),
(2, 'Ble', 'cereale', '2026-04-08', '2026-04-10', 'ceeee', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260407213250', '2026-04-07 23:37:06', 17);

-- --------------------------------------------------------

--
-- Structure de la table `equipement`
--

CREATE TABLE `equipement` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `prix` float DEFAULT NULL,
  `disponibilite` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `equipement`
--

INSERT INTO `equipement` (`id`, `nom`, `type`, `prix`, `disponibilite`, `user_id`) VALUES
(17, 'testttcgvh bjn,;', 'tracteur', 10, 'En maintenance', NULL),
(18, 'Test Machine', 'Test', 100, 'Indisponible', NULL),
(19, 'Test Machine', 'Test', 100, 'Disponible', NULL),
(26, 'Test Machine', 'Test', 100, 'Disponible', 1),
(27, 'Test Machine', 'Test', 100, 'Disponible', 1),
(28, 'Test Machine', 'Test', 100, 'Disponible', 1),
(29, 'Test Machine', 'Test', 100, 'Disponible', 1),
(30, 'ijhbhj', 'jjjj', 7777, 'Indisponible', 3),
(31, 'pppp', 'mmmm', 777, 'Disponible', 1),
(33, 'Test Machine', 'Test', 100, 'Disponible', 1),
(34, 'Test Machinee', 'Test', 100, 'Disponible', 1),
(35, 'Test Machine', 'Test', 100, 'Disponible', 1),
(36, 'rtere', 'yhjkl', 525, 'En maintenance', 1),
(37, 'llllll', 'tttttttttttttt', 111111, 'En maintenance', 1),
(38, 'ffffff', 'nnnnnn', 55555, 'Disponible', 1),
(41, '888', '888', 10, 'Disponible', NULL),
(42, 'tracteuuuuuur', 'tracteuuuur', 100000000, 'Indisponible', NULL),
(44, 'rrrrrrrrrrrrrr', 'rrrrrrrrrrrr', 852, 'En maintenance', NULL),
(45, '852', '8520', 888, 'Disponible', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `equipement_geo`
--

CREATE TABLE `equipement_geo` (
  `equipement_id` int(11) NOT NULL,
  `garage_id` int(11) DEFAULT NULL,
  `position_gps` varchar(50) DEFAULT NULL,
  `statut_garage` varchar(20) DEFAULT 'DANS_GARAGE',
  `derniere_localisation` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `equipement_geo`
--

INSERT INTO `equipement_geo` (`equipement_id`, `garage_id`, `position_gps`, `statut_garage`, `derniere_localisation`) VALUES
(35, 6, NULL, 'EN_DEPLACEMENT', '2026-03-05 00:42:14');

-- --------------------------------------------------------

--
-- Structure de la table `garage`
--

CREATE TABLE `garage` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `capacite` int(11) DEFAULT 10,
  `responsable` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `garage`
--

INSERT INTO `garage` (`id`, `nom`, `adresse`, `latitude`, `longitude`, `capacite`, `responsable`, `telephone`, `date_creation`) VALUES
(1, 'Garage Central', 'Zone Industrielle, Tunis', 36.8065, 10.1815, 20, 'Mohamed Ali', '71 234 567', '2026-03-05 00:04:44'),
(2, 'Garage Nord', 'Route de Bizerte, Ariana', 36.8665, 10.1655, 15, 'Sami Ben Salah', '72 345 678', '2026-03-05 00:04:44'),
(3, 'Garage Sud', 'Autoroute A1, Enfidha', 36.3665, 10.3815, 25, 'Karim Jebali', '73 456 789', '2026-03-05 00:04:44'),
(4, 'Garage Sousse', 'Route Touristique, Sousse', 35.8333, 10.6333, 12, 'Hichem Gharbi', '73 567 890', '2026-03-05 00:04:44'),
(5, 'Garage Sfax', 'Route Gabès, Sfax', 34.7333, 10.7667, 18, 'Ahmed Karray', '74 678 901', '2026-03-05 00:04:44'),
(6, 'test', 'el mourouj', 44.6666, 20.6654, 20, 'mohamed', '51037288', '2026-03-05 00:32:19');

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `nourriture`
--

CREATE TABLE `nourriture` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `nutritional_value` varchar(255) DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `nourriture`
--

INSERT INTO `nourriture` (`id`, `name`, `type`, `quantity`, `unit`, `nutritional_value`, `expiry_date`, `supplier`, `cost`, `date_added`, `is_active`) VALUES
(1, 'aaa', 'acb', 15.00, 'a', '4', '2026-04-07 19:30:00', 'bhd', 10.00, '2026-04-07 15:30:22', 1),
(2, 'oooooooooo', 'gfhj', 8520.00, 'hjk', NULL, NULL, '41', 852.00, '2026-04-07 23:13:57', 1);

-- --------------------------------------------------------

--
-- Structure de la table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` float NOT NULL,
  `status` varchar(50) DEFAULT 'PENDING',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `order_item`
--

CREATE TABLE `order_item` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` float NOT NULL,
  `price_at_purchase` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parcelle`
--

CREATE TABLE `parcelle` (
  `id_parcelle` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `superficie` double NOT NULL,
  `localisation` varchar(255) DEFAULT NULL,
  `type_sol` varchar(100) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `parcelle`
--

INSERT INTO `parcelle` (`id_parcelle`, `nom`, `superficie`, `localisation`, `type_sol`, `utilisateur_id`, `latitude`, `longitude`) VALUES
(5, 'dtfghj', 12.5, 'iergfbhj', 'ehgrvunf,izgbuhzef ', 1, 36.5478, 10.2874),
(6, 'Aeeeee', 45, 'ffff', 'arft', 2, 30, 10);

-- --------------------------------------------------------

--
-- Structure de la table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` float NOT NULL,
  `quantity` float NOT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT '­ƒìÄ',
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `price` float NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `quantity`, `image`, `seller_id`, `category`, `supplier`, `expiry_date`) VALUES
(1, 'test', 'test', 17, 16, NULL, 1, 'FRUITS', 'mohamed', '2028-12-09'),
(2, 'test', 'test', 17, 17, NULL, 1, 'FRUITS', 'mohamed', '2028-12-09'),
(3, 'batata', '7lowa', 1.2, 398, 'cea9746f-90a6-4574-8e54-b5da1f088e2c-69d4c80980e3c.jpg', 1, 'VEGETABLES', 'lemby', '2027-01-17'),
(4, 'bsal', 'reb3i', 20, 300, 'pngtree-shallot-or-red-onion-png-transparent-png-image-6402672-69d4c6a261a4a.jpg', 1, 'VEGETABLES', '5lifa', '2027-03-03'),
(5, 'fa9ous', 'reb3i', 12, 500, 'pngtree-shallot-or-red-onion-png-transparent-png-image-6402672-69d62d332b50d.jpg', 1, 'VEGETABLES', 'kamel', '2026-09-14');

-- --------------------------------------------------------

--
-- Structure de la table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `price_category` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ratings`
--

INSERT INTO `ratings` (`id`, `product_id`, `user_id`, `rating`, `comment`, `price_category`, `created_at`) VALUES
(1, 5, 1, 4, 'ya3tik el sa7a  sem7', 'LOW', '2026-04-08 12:30:21'),
(2, 3, 1, 5, 'mel7a', 'HIGH', '2026-04-08 12:41:54');

-- --------------------------------------------------------

--
-- Structure de la table `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `date_review` date DEFAULT NULL,
  `equipement_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `review`
--

INSERT INTO `review` (`id`, `commentaire`, `note`, `date_review`, `equipement_id`, `user_id`) VALUES
(10, 'jjjjjjjjjjjjj', 5, '2026-03-02', 29, 1),
(16, 'poiugfdcvb', 3, '2026-03-26', 38, 4),
(17, NULL, 3, '2026-04-05', 35, 1),
(18, 'yghjklm;m:', 5, '2026-04-08', 37, 1);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('ADMIN','USER') DEFAULT 'USER',
  `ferme_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `ferme_id`) VALUES
(1, 'Chef', 'Admin', 'admin@agrimans.com', '123', 'ADMIN', NULL),
(2, 'Agriculteur', 'Jean', 'jean@agrimans.com', '123', '', NULL),
(3, 'Cultivatrice', 'Marie', 'marie@agrimans.com', '123', '', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('ADMIN','USER') NOT NULL DEFAULT 'USER',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Administrateur', 'admin@agrimans.com', '00000000', '$2a$10$AhmY/l1.QGQIEixPIgp8GO4JEyrhJTDOSLMmkEChquBdZ.z82PQPS', 'ADMIN', '2026-03-06 17:14:24'),
(2, 'Jean Agriculteur', 'jean@agrimans.com', '22334455', '$2a$10$5JrMpzq4dJGJpE.ywJ/5..3jPpX6j1CRnAi2u6.9y.zJpq2LRvnpO', 'USER', '2026-03-06 17:14:24'),
(3, 'mohamed', 'hama22vv90@gmail.com', '51037288', '$2a$10$4cmu63xFsmSqcO9d7LWjz.idilKWF/3rrEH1cJIWvewMZMVELH9L.', 'USER', '2026-03-07 01:43:53'),
(4, 'taha', 'goodg8028@gmail.com', '51037288', '$2a$10$WMIhb3G.9KZuby4nOU0q8u3U3.Ya9uHaagYBwHjWxo0iF4eqGbqPK', 'USER', '2026-03-26 13:15:53');

-- --------------------------------------------------------

--
-- Structure de la table `user_auth`
--

CREATE TABLE `user_auth` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_auth`
--

INSERT INTO `user_auth` (`id`, `email`, `roles`, `password`) VALUES
(1, 'admin@agrimans.com', '[\"ROLE_ADMIN\"]', '$2y$13$c3b9X6Y3t0KhIK3XTEVVCO.d97zQmmzJmHix9J5hBxhuFXr.naAL.'),
(2, 'user@agrimans.com', '[\"ROLE_USER\"]', '$2y$13$CXQtkBcmOjBOdDPjEBwTMu2JiKvlXhccbTy7UD5ETVg0iNVvpNGP.'),
(3, 'test@agrimans.com', '[\"ROLE_ADMIN\"]', '$2y$13$Ll3cUHBxReurue04l8i.zOopqGDlHfk7UCdhpZZ3trB6xITzSsFIe'),
(4, 'testuser@agrimans.com', '[\"ROLE_USER\"]', '$2y$13$1Lv6vb4GZm3bi8EysmB0mOhdnL.0yyi1oSczh06PXm9dAe4A/vPYu');

-- --------------------------------------------------------

--
-- Structure de la table `user_otp`
--

CREATE TABLE `user_otp` (
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `animal`
--
ALTER TABLE `animal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_animal_user` (`userId`);

--
-- Index pour la table `animal_nourriture`
--
ALTER TABLE `animal_nourriture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A9B23EF78E962C16` (`animal_id`),
  ADD KEY `IDX_A9B23EF798BD5834` (`nourriture_id`);

--
-- Index pour la table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Index pour la table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `culture`
--
ALTER TABLE `culture`
  ADD PRIMARY KEY (`id_culture`),
  ADD KEY `fk_culture_parcelle` (`parcelle_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `equipement`
--
ALTER TABLE `equipement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_equipement_user` (`user_id`);

--
-- Index pour la table `equipement_geo`
--
ALTER TABLE `equipement_geo`
  ADD PRIMARY KEY (`equipement_id`),
  ADD KEY `garage_id` (`garage_id`);

--
-- Index pour la table `garage`
--
ALTER TABLE `garage`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Index pour la table `nourriture`
--
ALTER TABLE `nourriture`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `parcelle`
--
ALTER TABLE `parcelle`
  ADD PRIMARY KEY (`id_parcelle`),
  ADD KEY `fk_parcelle_user` (`utilisateur_id`);

--
-- Index pour la table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Index pour la table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_review_users_new` (`user_id`),
  ADD KEY `FK_794381C6806F0F5C` (`equipement_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_auth`
--
ALTER TABLE `user_auth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_825FFC90E7927C74` (`email`);

--
-- Index pour la table `user_otp`
--
ALTER TABLE `user_otp`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `animal`
--
ALTER TABLE `animal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `animal_nourriture`
--
ALTER TABLE `animal_nourriture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `culture`
--
ALTER TABLE `culture`
  MODIFY `id_culture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `equipement`
--
ALTER TABLE `equipement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT pour la table `garage`
--
ALTER TABLE `garage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `nourriture`
--
ALTER TABLE `nourriture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `parcelle`
--
ALTER TABLE `parcelle`
  MODIFY `id_parcelle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `user_auth`
--
ALTER TABLE `user_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `animal_nourriture`
--
ALTER TABLE `animal_nourriture`
  ADD CONSTRAINT `FK_A9B23EF78E962C16` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`),
  ADD CONSTRAINT `FK_A9B23EF798BD5834` FOREIGN KEY (`nourriture_id`) REFERENCES `nourriture` (`id`);

--
-- Contraintes pour la table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_buyer_id` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `FK_BEF484451AD5CDBF` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_BEF484454584665A` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_items_cart_id` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `equipement`
--
ALTER TABLE `equipement`
  ADD CONSTRAINT `fk_equipement_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `equipement_geo`
--
ALTER TABLE `equipement_geo`
  ADD CONSTRAINT `FK_7ED7FDDF806F0F5C` FOREIGN KEY (`equipement_id`) REFERENCES `equipement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipement_geo_ibfk_1` FOREIGN KEY (`equipement_id`) REFERENCES `equipement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipement_geo_ibfk_2` FOREIGN KEY (`garage_id`) REFERENCES `garage` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_seller_id` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `FK_CEB607C94584665A` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ratings_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `FK_794381C6806F0F5C` FOREIGN KEY (`equipement_id`) REFERENCES `equipement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_users_new` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`equipement_id`) REFERENCES `equipement` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
