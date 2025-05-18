<?php
// Define base path for assets
define('BASE_PATH', dirname($_SERVER['PHP_SELF']));

session_start();
ob_start(); // Mulai output buffering
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Simple routing
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Map pages to their respective PHP files
$pages = [
    'home' => 'pages/home.php',
    'about' => 'pages/about.php',
    'services' => 'pages/services.php',
    'contact' => 'pages/contact.php',
    'login' => 'pages/login.php',
    'signup' => 'pages/signup.php',
    'dashboard' => 'pages/dashboard.php',
    'order-form' => 'pages/order-form.php',
    'order-service' => 'pages/order-service.php',
    'view-invoice' => 'pages/view-invoice.php',
    'edit-order' => 'pages/edit-order.php',
    'logout' => 'pages/logout.php',
    'edit-profile' => 'pages/edit-profile.php',
    'change-password' => 'pages/change-password.php',
    'forgot-password' => 'pages/forgot-password.php',
    'reset-password' => 'pages/reset-password.php',
    // Admin pages
    'admin-dashboard' => 'admin/dashboard.php',
    'admin-users' => 'admin/users.php',
    'admin-services' => 'admin/services.php',
    'admin-orders' => 'admin/orders.php',
    'admin-testimonials' => 'admin/testimonials.php',
    'admin-messages' => 'admin/messages.php',
    // Setup page
    'setup' => 'setup.php'
];

// Check if the requested page exists
if (isset($pages[$page])) {
    // Check if admin page is requested
    if (strpos($page, 'admin-') === 0 && !$auth->isAdmin()) {
        // Redirect to login if not admin
        header('Location: index.php?page=login');
        exit;
    }
    
    // Check if user page is requested
    if (in_array($page, ['dashboard', 'order-service', 'view-invoice', 'edit-order', 'edit-profile', 'change-password']) && !$auth->isLoggedIn()) {
        // Redirect to login if not logged in
        header('Location: index.php?page=login');
        exit;
    }
    
    // Special case for setup page - don't include header/footer
    if ($page === 'setup') {
        include $pages[$page];
    } else {
        require_once __DIR__ . '/includes/header.php';
        include $pages[$page];
        require_once __DIR__ . '/includes/footer.php';
    }
} else {
    // 404 page not found
    require_once __DIR__ . '/includes/header.php';
    include 'pages/404.php';
    require_once __DIR__ . '/includes/footer.php';
}
?>
