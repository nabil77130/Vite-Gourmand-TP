-- Database Schema for Julie's Catering
-- Compatible with MariaDB / MySQL

CREATE DATABASE IF NOT EXISTS julies_catering;
USE julies_catering;

-- Users Table
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    address VARCHAR(255),
    city VARCHAR(100),
    zip_code VARCHAR(20),
    phone VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Products Table (Dishes)
CREATE TABLE IF NOT EXISTS product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_name VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    category ENUM('starter', 'main', 'dessert', 'drink') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Orders Table
CREATE TABLE IF NOT EXISTS `order` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'accepted', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id)
) ENGINE=InnoDB;

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_order DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES `order`(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(id)
) ENGINE=InnoDB;

-- Reviews Table
CREATE TABLE IF NOT EXISTS review (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES `order`(id) ON DELETE CASCADE
) ENGINE=InnoDB;
