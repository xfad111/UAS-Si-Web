-- Create tables in the current database (infaid_admin)
-- No CREATE DATABASE or USE statements since we're working with an existing database

-- Users table - ensuring it has all necessary fields including is_admin
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    requirements TEXT,
    quantity INT NOT NULL DEFAULT 1,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    rating INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0
);

-- Insert sample data only if services table is empty
INSERT INTO services (name, category, short_description, description, price, image_url, featured)
SELECT * FROM (
    SELECT 'Web Design' as name, 'Design' as category, 
           'Professional web design services for your business' as short_description, 
           'Our web design services include custom design, responsive layouts, and user experience optimization to create a website that represents your brand and engages your audience.' as description,
           999.99 as price, 'assets/images/services/web-design.jpg' as image_url, 1 as featured
) AS tmp
WHERE NOT EXISTS (
    SELECT name FROM services WHERE name = 'Web Design'
) LIMIT 1;

INSERT INTO services (name, category, short_description, description, price, image_url, featured)
SELECT * FROM (
    SELECT 'Web Development' as name, 'Development' as category, 
           'Custom web development solutions' as short_description, 
           'We build custom web applications and websites using the latest technologies and best practices to ensure your project is scalable, secure, and maintainable.' as description,
           1499.99 as price, 'assets/images/services/web-development.jpg' as image_url, 1 as featured
) AS tmp
WHERE NOT EXISTS (
    SELECT name FROM services WHERE name = 'Web Development'
) LIMIT 1;

INSERT INTO services (name, category, short_description, description, price, image_url, featured)
SELECT * FROM (
    SELECT 'SEO Optimization' as name, 'Marketing' as category, 
           'Improve your search engine rankings' as short_description, 
           'Our SEO services help improve your website\'s visibility in search engines, driving more organic traffic and potential customers to your business.' as description,
           799.99 as price, 'assets/images/services/seo.jpg' as image_url, 1 as featured
) AS tmp
WHERE NOT EXISTS (
    SELECT name FROM services WHERE name = 'SEO Optimization'
) LIMIT 1;
