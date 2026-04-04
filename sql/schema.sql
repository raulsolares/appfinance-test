-- Esquema de Base de Datos para Finanzas Pro

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Cuentas (Tarjetas, Bancos, Efectivo)
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('cash', 'credit', 'bank') NOT NULL,
  `balance` DECIMAL(15,2) DEFAULT 0.00,
  `color` VARCHAR(20) DEFAULT '#3b82f6', -- Color para gráficas
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Categorías (Personalizables)
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('income', 'expense') NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'wallet', -- Nombre de icono Lucide/HeroIcons
  `color` VARCHAR(20) DEFAULT '#10b981',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Transacciones
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `account_id` INT NOT NULL,
  `category_id` INT DEFAULT NULL, -- NULL para traspasos
  `amount` DECIMAL(15,2) NOT NULL,
  `type` ENUM('income', 'expense', 'transfer') NOT NULL,
  `description` TEXT,
  `date` DATE NOT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL, -- Ruta a la factura/ticket
  `transfer_id` INT DEFAULT NULL, -- ID de la transacción vinculada en traspasos
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Deudas
CREATE TABLE IF NOT EXISTS `debts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `type` ENUM('to_pay', 'to_receive') NOT NULL,
  `status` ENUM('pending', 'paid', 'partially_paid') DEFAULT 'pending',
  `due_date` DATE DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
