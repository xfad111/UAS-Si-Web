-- First, make sure the users table exists
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Check if the is_admin column exists
SELECT COUNT(*) INTO @column_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME = 'is_admin';

-- Add the is_admin column if it doesn't exist
SET @sql = IF(@column_exists = 0, 'ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0', 'SELECT "Column already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if admin user exists
SELECT COUNT(*) INTO @admin_exists 
FROM users 
WHERE email = 'admin@serviceco.com';

-- Create admin user if it doesn't exist
SET @sql = IF(@admin_exists = 0, 
    'INSERT INTO users (name, email, password, created_at, is_admin) VALUES ("Super Admin", "admin@serviceco.com", "$2y$10$Hl9y1YyRJyDyOzlx2OqbO.4UjGxt5IrFIBVLGJPLTMQwgDJ3KQuUy", NOW(), 1)',
    'UPDATE users SET is_admin = 1 WHERE email = "admin@serviceco.com"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
