-- Script de mise à jour de la table products pour le marketplace
-- À exécuter dans phpMyAdmin ou via MySQL

USE agrimans;

-- Modifier le type de quantity de float à int
ALTER TABLE `products` MODIFY `quantity` int(11) NOT NULL;

-- Supprimer les anciennes colonnes inutiles
ALTER TABLE `products` DROP COLUMN `category_id`;
ALTER TABLE `products` DROP COLUMN `user_id`;
ALTER TABLE `products` DROP COLUMN `created_at`;
ALTER TABLE `products` DROP COLUMN `updated_at`;

-- Ajouter les nouvelles colonnes
ALTER TABLE `products` ADD `image` varchar(255) DEFAULT NULL AFTER `quantity`;
ALTER TABLE `products` ADD `seller_id` int(11) DEFAULT NULL AFTER `image`;
ALTER TABLE `products` ADD `category` varchar(50) NOT NULL AFTER `seller_id`;
ALTER TABLE `products` ADD `supplier` varchar(255) DEFAULT NULL AFTER `category`;
ALTER TABLE `products` ADD `expiry_date` date DEFAULT NULL AFTER `supplier`;

-- Ajouter l'index pour seller_id
ALTER TABLE `products` ADD KEY `seller_id` (`seller_id`);

-- Ajouter la contrainte étrangère
ALTER TABLE `products` ADD CONSTRAINT `fk_products_seller_id` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;