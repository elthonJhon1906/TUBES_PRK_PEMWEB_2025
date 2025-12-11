DROP DATABASE IF EXISTS npc_printing_db;
CREATE DATABASE npc_printing_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE npc_printing_db;

CREATE TABLE app_settings (
  id int PRIMARY KEY AUTO_INCREMENT,
  app_name varchar(100) DEFAULT 'NPC Printing',
  app_description text,
  address text,
  phone_whatsapp varchar(20),
  email_contact varchar(100),
  logo_image varchar(255),
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO app_settings (app_name, phone_whatsapp, address) 
VALUES ('Nagoya Print & Copy', 621234567890, 'Jl. Kampung Baru. 1, Bandar Lampung');

CREATE TABLE users (
  id int PRIMARY KEY AUTO_INCREMENT,
  username varchar(50) UNIQUE NOT NULL,
  password varchar(255) NOT NULL,
  full_name varchar(100) NOT NULL,
  email varchar(100) UNIQUE,
  phone varchar(20),
  role ENUM('admin', 'staff', 'customer', 'owner') NOT NULL DEFAULT 'customer',
  is_active boolean DEFAULT true,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, full_name, email, role) 
VALUES ('admin', 'admin123', 'Super Admin', 'admin@npc.com', 'admin'), 
('staff', 'staff123', 'Staff Kece', 'staff@npc.com', 'staff'),
('owner', 'owner123', 'Owner Kece', 'owner@npc.com', 'owner'),
('edfa', '123', 'Edfa', 'edfa@edfa.com', 'customer');

CREATE TABLE products (
  id int PRIMARY KEY AUTO_INCREMENT,
  code varchar(20) UNIQUE NOT NULL,
  name varchar(100) NOT NULL,
  category varchar(50) NOT NULL,
  unit varchar(20) NOT NULL,
  current_stock decimal(10,2) DEFAULT 0,
  min_stock int DEFAULT 5,
  selling_price decimal(10,2) NOT NULL,
  image varchar(255),
  is_active boolean DEFAULT true,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product_batches (
  id int PRIMARY KEY AUTO_INCREMENT,
  product_id int NOT NULL,
  batch_code varchar(50) NOT NULL,
  purchase_date date NOT NULL,
  purchase_price decimal(12,2) NOT NULL,
  quantity decimal(10,2) NOT NULL,
  quantity_remaining decimal(10,2) NOT NULL,
  is_active boolean DEFAULT true,
  FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

CREATE TABLE services (
  id int PRIMARY KEY AUTO_INCREMENT,
  code varchar(20) UNIQUE NOT NULL,
  name varchar(100) NOT NULL,
  category varchar(50) NOT NULL,
  description text,
  base_price decimal(10,2) NOT NULL,
  image varchar(255),
  unit varchar(20) DEFAULT 'lembar',
  estimated_minutes int DEFAULT 5,
  is_active boolean DEFAULT true
);

CREATE TABLE orders (
  id int PRIMARY KEY AUTO_INCREMENT,
  order_code varchar(20) UNIQUE NOT NULL,
  pickup_code varchar(6) UNIQUE NOT NULL,
  customer_id int NOT NULL,
  staff_id int DEFAULT NULL,
  total_amount decimal(12,2) DEFAULT 0,
  paid_amount decimal(12,2) DEFAULT 0,
  status ENUM('pending', 'processing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
  payment_status ENUM('unpaid', 'partial', 'paid', 'verified') DEFAULT 'unpaid',
  notes text,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users (id),
  FOREIGN KEY (staff_id) REFERENCES users (id)
);

CREATE TABLE order_items (
  id int PRIMARY KEY AUTO_INCREMENT,
  order_id int NOT NULL,
  item_type ENUM('service', 'product') NOT NULL DEFAULT 'service',
  service_id int DEFAULT NULL,
  product_id int DEFAULT NULL,
  quantity decimal(10,2) NOT NULL,
  unit_price decimal(10,2) NOT NULL,
  subtotal decimal(12,2) NOT NULL,
  specifications text,
  upload_type ENUM('none', 'file', 'link') DEFAULT 'none',
  file_path varchar(255),
  file_name varchar(255),
  file_mime varchar(100),
  file_link varchar(255),
  item_status ENUM('pending', 'processing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
  FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES services (id),
  FOREIGN KEY (product_id) REFERENCES products (id),
  CONSTRAINT chk_order_items_type
    CHECK (
      (item_type = 'service' AND service_id IS NOT NULL AND product_id IS NULL) OR
      (item_type = 'product' AND product_id IS NOT NULL AND service_id IS NULL)
    )
);

CREATE TABLE order_payment_logs (
  id int PRIMARY KEY AUTO_INCREMENT,
  transaction_code varchar(20) UNIQUE NOT NULL,
  order_id int NOT NULL,
  amount decimal(12,2) NOT NULL,
  method ENUM('cash', 'transfer') NOT NULL,
  proof_image varchar(255), 
  verified_by int,
  verified_at datetime,
  status ENUM('pending', 'valid', 'invalid') DEFAULT 'valid',
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users (id)
);

CREATE TABLE expenses (
  id int PRIMARY KEY AUTO_INCREMENT,
  expense_date date NOT NULL,
  description varchar(255) NOT NULL,
  category ENUM('purchasing', 'operational', 'salary', 'maintenance', 'other') NOT NULL,
  amount decimal(12,2) NOT NULL,
  created_by int NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users (id)
);

CREATE TABLE system_logs (
  id int PRIMARY KEY AUTO_INCREMENT,
  user_id int NOT NULL,
  username varchar(50) NOT NULL,
  role varchar(20) NOT NULL,
  action_type varchar(50) NOT NULL,
  target_id int NULL,
  description text,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_log_user ON system_logs (user_id);
CREATE INDEX idx_log_action ON system_logs (action_type);
CREATE INDEX idx_log_date ON system_logs (created_at);

CREATE INDEX idx_order_status ON orders (status);
CREATE INDEX idx_pickup_code ON orders (pickup_code);
CREATE INDEX idx_user_role ON users (role);
CREATE INDEX idx_prod_stock ON products (current_stock);

CREATE INDEX idx_orders_payment ON orders (payment_status);
CREATE INDEX idx_orders_date ON orders (created_at);
CREATE INDEX idx_orders_customer ON orders (customer_id);

CREATE INDEX idx_prod_category ON products (category);
