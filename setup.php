<?php
// Database connection settings - update with your actual values
$host = 'localhost';
$db_name = 'infaid_admin'; // Your database name
$username = 'infaid_admin'; // Your database username
$password = ''; // Your database password - fill in the actual password

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<h2>Database Setup</h2>";
    
    // Create users table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<p>✓ Users table check completed</p>";
    
    // Check if is_admin column exists
    $checkColumn = $conn->query("
        SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'is_admin'
    ");
    $columnExists = $checkColumn->fetch()['count'];
    
    if ($columnExists == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0");
        echo "<p>✓ Added is_admin column to users table</p>";
    } else {
        echo "<p>✓ is_admin column already exists</p>";
    }
    
    // Create admin user
    $name = 'Super Admin';
    $email = 'admin@serviceco.com';
    $password = 'Admin@111';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $is_admin = 1;
    
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Update existing admin to ensure is_admin is set to 1
        $stmt = $conn->prepare("UPDATE users SET is_admin = 1 WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        echo "<p>✓ Admin user already exists - ensured admin privileges</p>";
    } else {
        // Insert admin user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin, created_at) VALUES (:name, :email, :password, :is_admin, NOW())");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':is_admin', $is_admin);
        $stmt->execute();
        echo "<p>✓ Admin user created successfully</p>";
    }
    
    // Create regular user for testing
    $name = 'John Doe';
    $email = 'john@example.com';
    $password = 'password123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $is_admin = 0;
    
    // Check if test user already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "<p>✓ Test user already exists</p>";
    } else {
        // Insert test user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin, created_at) VALUES (:name, :email, :password, :is_admin, NOW())");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':is_admin', $is_admin);
        $stmt->execute();
        echo "<p>✓ Test user created successfully</p>";
    }
    
    // Check if other required tables exist and create them if needed
    $tables = [
        'password_resets' => "
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        'services' => "
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
            )
        ",
        'orders' => "
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
            )
        ",
        'testimonials' => "
            CREATE TABLE IF NOT EXISTS testimonials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                content TEXT NOT NULL,
                rating INT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ",
        'contact_messages' => "
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                subject VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0
            )
        "
    ];
    
    foreach ($tables as $table => $sql) {
        $conn->exec($sql);
        echo "<p>✓ {$table} table check completed</p>";
    }
    
    // Add sample services if the services table is empty
    $stmt = $conn->query("SELECT COUNT(*) as count FROM services");
    $serviceCount = $stmt->fetch()['count'];
    
    if ($serviceCount == 0) {
        $sampleServices = [
            [
                'name' => 'Web Design',
                'category' => 'Design',
                'short_description' => 'Professional web design services for your business',
                'description' => 'Our web design services include custom design, responsive layouts, and user experience optimization to create a website that represents your brand and engages your audience.',
                'price' => 999.99,
                'image_url' => 'assets/images/services/web-design.jpg',
                'featured' => 1
            ],
            [
                'name' => 'Web Development',
                'category' => 'Development',
                'short_description' => 'Custom web development solutions',
                'description' => 'We build custom web applications and websites using the latest technologies and best practices to ensure your project is scalable, secure, and maintainable.',
                'price' => 1499.99,
                'image_url' => 'assets/images/services/web-development.jpg',
                'featured' => 1
            ],
            [
                'name' => 'SEO Optimization',
                'category' => 'Marketing',
                'short_description' => 'Improve your search engine rankings',
                'description' => 'Our SEO services help improve your website\'s visibility in search engines, driving more organic traffic and potential customers to your business.',
                'price' => 799.99,
                'image_url' => 'assets/images/services/seo.jpg',
                'featured' => 1
            ]
        ];
        
        $stmt = $conn->prepare("
            INSERT INTO services 
            (name, category, short_description, description, price, image_url, featured, created_at) 
            VALUES 
            (:name, :category, :short_description, :description, :price, :image_url, :featured, NOW())
        ");
        
        foreach ($sampleServices as $service) {
            $stmt->bindParam(':name', $service['name']);
            $stmt->bindParam(':category', $service['category']);
            $stmt->bindParam(':short_description', $service['short_description']);
            $stmt->bindParam(':description', $service['description']);
            $stmt->bindParam(':price', $service['price']);
            $stmt->bindParam(':image_url', $service['image_url']);
            $stmt->bindParam(':featured', $service['featured']);
            $stmt->execute();
        }
        
        echo "<p>✓ Sample services added</p>";
    } else {
        echo "<p>✓ Services already exist</p>";
    }
    
    echo "<h3 style='color: green;'>Setup completed successfully!</h3>";
    echo "<p>You can now <a href='index.php'>go to the homepage</a> or <a href='index.php?page=login'>login</a> with:</p>";
    echo "<ul>";
    echo "<li>Admin: admin@serviceco.com / Admin@111</li>";
    echo "<li>User: john@example.com / password123</li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>Setup Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials and ensure your database server is running.</p>";
}
?>
