CREATE TABLE `user_roles` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `level` int NOT NULL,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `users` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(50) UNIQUE NOT NULL,
  `email` varchar(100) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20),
  `role_id` int NOT NULL,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `product_categories` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `products` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int NOT NULL,
  `unit` varchar(20) NOT NULL,
  `current_stock` decimal(10,3) DEFAULT 0,
  `min_stock` int DEFAULT 0,
  `current_price` decimal(10,2),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `product_batches` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `batch_code` varchar(30) NOT NULL,
  `purchase_date` date NOT NULL,
  `purchase_price` decimal(12,2) NOT NULL,
  `quantity` decimal(10,3) NOT NULL,
  `quantity_used` decimal(10,3) DEFAULT 0,
  `quantity_remaining` decimal(10,3),
  `selling_price` decimal(10,2) NOT NULL,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `service_categories` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `services` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int NOT NULL,
  `description` text,
  `base_price` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT 'lembar',
  `estimated_minutes` int,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `production_statuses` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `sequence_order` int NOT NULL,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `transaction_statuses` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `orders` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `order_code` varchar(20) UNIQUE NOT NULL,
  `customer_id` int NOT NULL,
  `pickup_code` varchar(8) NOT NULL,
  `order_date` datetime DEFAULT (CURRENT_TIMESTAMP),
  `customer_notes` text,
  `customer_file_url` varchar(255),
  `production_status_id` int NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `transaction_status_id` int NOT NULL,
  `staff_id` int,
  `completed_at` datetime,
  `cancelled_at` datetime,
  `cancellation_reason` text,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP),
  `updated_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `order_items` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `service_id` int NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `production_status_id` int NOT NULL,
  `specifications` json,
  `notes` text,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `transaction_methods` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `needs_proof` boolean DEFAULT false,
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `proof_types` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(30) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `verification_statuses` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `transactions` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `transaction_code` varchar(20) UNIQUE NOT NULL,
  `order_id` int NOT NULL,
  `transaction_date` datetime DEFAULT (CURRENT_TIMESTAMP),
  `transaction_method_id` int NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `change_amount` decimal(12,2) DEFAULT 0,
  `proof_type_id` int NOT NULL,
  `proof_image_url` varchar(255) NOT NULL,
  `bank_name` varchar(50),
  `account_name` varchar(100),
  `reference_number` varchar(100),
  `verification_status_id` int NOT NULL,
  `verified_by` int,
  `verified_at` datetime,
  `staff_id` int NOT NULL,
  `notes` text,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `expense_categories` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(30) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `expense_methods` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255),
  `is_active` boolean DEFAULT true,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE TABLE `expenses` (
  `id` int PRIMARY KEY AUTO_INCREMENT,
  `expense_code` varchar(20) UNIQUE NOT NULL,
  `expense_date` date NOT NULL,
  `category_id` int NOT NULL,
  `product_id` int,
  `product_batch_id` int,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `transaction_method_id` int NOT NULL,
  `created_by` int NOT NULL,
  `notes` text,
  `created_at` timestamp DEFAULT (CURRENT_TIMESTAMP)
);

CREATE INDEX `user_roles_index_0` ON `user_roles` (`code`);

CREATE INDEX `user_roles_index_1` ON `user_roles` (`level`);

CREATE INDEX `users_index_2` ON `users` (`username`);

CREATE INDEX `users_index_3` ON `users` (`email`);

CREATE INDEX `users_index_4` ON `users` (`role_id`);

CREATE INDEX `product_categories_index_5` ON `product_categories` (`code`);

CREATE INDEX `products_index_6` ON `products` (`code`);

CREATE INDEX `products_index_7` ON `products` (`category_id`);

CREATE INDEX `products_index_8` ON `products` (`is_active`);

CREATE INDEX `product_batches_index_9` ON `product_batches` (`product_id`);

CREATE INDEX `product_batches_index_10` ON `product_batches` (`batch_code`);

CREATE INDEX `product_batches_index_11` ON `product_batches` (`is_active`);

CREATE INDEX `service_categories_index_12` ON `service_categories` (`code`);

CREATE INDEX `services_index_13` ON `services` (`code`);

CREATE INDEX `services_index_14` ON `services` (`category_id`);

CREATE INDEX `services_index_15` ON `services` (`is_active`);

CREATE INDEX `production_statuses_index_16` ON `production_statuses` (`code`);

CREATE INDEX `production_statuses_index_17` ON `production_statuses` (`sequence_order`);

CREATE INDEX `transaction_statuses_index_18` ON `transaction_statuses` (`code`);

CREATE INDEX `orders_index_19` ON `orders` (`order_code`);

CREATE INDEX `orders_index_20` ON `orders` (`pickup_code`);

CREATE INDEX `orders_index_21` ON `orders` (`customer_id`);

CREATE INDEX `orders_index_22` ON `orders` (`transaction_status_id`);

CREATE INDEX `orders_index_23` ON `orders` (`order_date`);

CREATE INDEX `order_items_index_24` ON `order_items` (`order_id`);

CREATE INDEX `order_items_index_25` ON `order_items` (`service_id`);

CREATE INDEX `order_items_index_26` ON `order_items` (`production_status_id`);

CREATE INDEX `transaction_methods_index_27` ON `transaction_methods` (`code`);

CREATE INDEX `transaction_methods_index_28` ON `transaction_methods` (`needs_proof`);

CREATE INDEX `proof_types_index_29` ON `proof_types` (`code`);

CREATE INDEX `verification_statuses_index_30` ON `verification_statuses` (`code`);

CREATE INDEX `transactions_index_31` ON `transactions` (`transaction_code`);

CREATE INDEX `transactions_index_32` ON `transactions` (`order_id`);

CREATE INDEX `transactions_index_33` ON `transactions` (`transaction_date`);

CREATE INDEX `transactions_index_34` ON `transactions` (`transaction_method_id`);

CREATE INDEX `transactions_index_35` ON `transactions` (`verification_status_id`);

CREATE INDEX `transactions_index_36` ON `transactions` (`staff_id`);

CREATE INDEX `expense_categories_index_37` ON `expense_categories` (`code`);

CREATE INDEX `expense_methods_index_38` ON `expense_methods` (`code`);

CREATE INDEX `expenses_index_39` ON `expenses` (`expense_code`);

CREATE INDEX `expenses_index_40` ON `expenses` (`expense_date`);

CREATE INDEX `expenses_index_41` ON `expenses` (`category_id`);

ALTER TABLE `users` ADD FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`);

ALTER TABLE `products` ADD FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`);

ALTER TABLE `product_batches` ADD FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

ALTER TABLE `services` ADD FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`production_status_id`) REFERENCES `production_statuses` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`transaction_status_id`) REFERENCES `transaction_statuses` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`production_status_id`) REFERENCES `production_statuses` (`id`);

ALTER TABLE `transactions` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `transactions` ADD FOREIGN KEY (`transaction_method_id`) REFERENCES `transaction_methods` (`id`);

ALTER TABLE `transactions` ADD FOREIGN KEY (`proof_type_id`) REFERENCES `proof_types` (`id`);

ALTER TABLE `transactions` ADD FOREIGN KEY (`verification_status_id`) REFERENCES `verification_statuses` (`id`);

ALTER TABLE `transactions` ADD FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

ALTER TABLE `transactions` ADD FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

ALTER TABLE `expenses` ADD FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`);

ALTER TABLE `expenses` ADD FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

ALTER TABLE `expenses` ADD FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`);

ALTER TABLE `expenses` ADD FOREIGN KEY (`transaction_method_id`) REFERENCES `expense_methods` (`id`);

ALTER TABLE `expenses` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
