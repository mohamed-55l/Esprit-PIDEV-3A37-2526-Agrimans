-- Script de correction pour le module marketplace
-- Exécutez-le dans phpMyAdmin ou via MySQL après avoir sélectionné la base agrimans

USE agrimans;

-- 1) Mettre à jour la table products pour correspondre aux entités
ALTER TABLE `products`
  CHANGE `quantity` `quantity` int(11) NOT NULL,
  DROP COLUMN IF EXISTS `category_id`,
  DROP COLUMN IF EXISTS `user_id`,
  DROP COLUMN IF EXISTS `created_at`,
  DROP COLUMN IF EXISTS `updated_at`,
  ADD COLUMN IF NOT EXISTS `image` varchar(255) DEFAULT NULL AFTER `quantity`,
  ADD COLUMN IF NOT EXISTS `seller_id` int(11) DEFAULT NULL AFTER `image`,
  ADD COLUMN IF NOT EXISTS `category` varchar(50) NOT NULL AFTER `seller_id`,
  ADD COLUMN IF NOT EXISTS `supplier` varchar(255) DEFAULT NULL AFTER `category`,
  ADD COLUMN IF NOT EXISTS `expiry_date` date DEFAULT NULL AFTER `supplier`;

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`);

-- 2) Créer ou corriger la table carts
CREATE TABLE IF NOT EXISTS `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `buyer_id` (`buyer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3) Créer ou corriger la table cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4) Ajouter les clés étrangères
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_buyer_id` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_cart_id` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_items_product_id` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
