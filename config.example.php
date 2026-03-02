<?php
/**
 * Configuration File for Творческая Мастерская
 * 
 * This is a template configuration file.
 * Copy this file and customize it for your environment.
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================

/**
 * Database Type
 * Set to true to use MySQL, false to use JSON files
 */
define('USE_MYSQL', false);

/**
 * MySQL Database Configuration
 * Only used when USE_MYSQL is true
 */
define('DB_HOST', 'localhost');      // Database host (usually 'localhost')
define('DB_NAME', 'workshop_db');    // Database name
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password
define('DB_CHARSET', 'utf8mb4');     // Character set

/**
 * JSON Files Configuration
 * Only used when USE_MYSQL is false
 */
define('DB_FILE', 'database.json');     // Products database file
define('ORDERS_FILE', 'orders.json');   // Orders database file

// ============================================
// APPLICATION SETTINGS
// ============================================

/**
 * Application Environment
 * Values: 'development', 'production'
 */
define('APP_ENV', 'development');

/**
 * Debug Mode
 * Set to false in production
 */
define('DEBUG_MODE', true);

/**
 * Timezone
 */
date_default_timezone_set('Europe/Moscow');

// ============================================
// SECURITY SETTINGS
// ============================================

/**
 * CORS Settings
 * Allowed origins for Cross-Origin Resource Sharing
 */
define('ALLOWED_ORIGINS', [
    'http://localhost:8000',
    'http://127.0.0.1:8000',
    // Add your production domain here
    // 'https://yourdomain.com'
]);

/**
 * Admin Authentication
 * Set to true to require authentication for admin panel
 */
define('REQUIRE_ADMIN_AUTH', false);

/**
 * Admin Credentials (if REQUIRE_ADMIN_AUTH is true)
 * In production, use proper password hashing!
 */
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123'); // Change this!

// ============================================
// EMAIL SETTINGS (for order notifications)
// ============================================

/**
 * Enable Email Notifications
 */
define('EMAIL_ENABLED', false);

/**
 * SMTP Configuration
 */
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@example.com');
define('SMTP_PASSWORD', 'your-password');
define('SMTP_FROM_EMAIL', 'noreply@workshop.com');
define('SMTP_FROM_NAME', 'Творческая Мастерская');

/**
 * Admin Email (receives order notifications)
 */
define('ADMIN_EMAIL', 'admin@workshop.com');

// ============================================
// UPLOAD SETTINGS
// ============================================

/**
 * Maximum file upload size (in bytes)
 * 5MB = 5 * 1024 * 1024
 */
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);

/**
 * Allowed image types
 */
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

/**
 * Upload directory
 */
define('UPLOAD_DIR', 'uploads/');

// ============================================
// PAGINATION & LIMITS
// ============================================

/**
 * Items per page in admin panel
 */
define('ITEMS_PER_PAGE', 20);

/**
 * Maximum items in cart
 */
define('MAX_CART_ITEMS', 50);

// ============================================
// CURRENCY & LOCALIZATION
// ============================================

/**
 * Currency symbol
 */
define('CURRENCY_SYMBOL', '₽');

/**
 * Locale
 */
define('LOCALE', 'ru_RU');

// ============================================
// LOGGING
// ============================================

/**
 * Enable logging
 */
define('LOGGING_ENABLED', true);

/**
 * Log file path
 */
define('LOG_FILE', 'logs/app.log');

/**
 * Log level
 * Values: 'error', 'warning', 'info', 'debug'
 */
define('LOG_LEVEL', 'info');

// ============================================
// CACHE SETTINGS
// ============================================

/**
 * Enable caching
 */
define('CACHE_ENABLED', false);

/**
 * Cache duration in seconds
 * 3600 = 1 hour
 */
define('CACHE_DURATION', 3600);

// ============================================
// API SETTINGS
// ============================================

/**
 * API Rate Limiting
 * Maximum requests per minute
 */
define('API_RATE_LIMIT', 60);

/**
 * Enable API key authentication
 */
define('API_KEY_REQUIRED', false);

/**
 * API Keys (if API_KEY_REQUIRED is true)
 */
define('API_KEYS', [
    'your-api-key-here',
    'another-api-key'
]);

// ============================================
// CUSTOM SETTINGS
// ============================================

/**
 * Minimum order amount
 */
define('MIN_ORDER_AMOUNT', 500);

/**
 * Free shipping threshold
 */
define('FREE_SHIPPING_THRESHOLD', 3000);

/**
 * Shipping cost
 */
define('SHIPPING_COST', 300);

/**
 * Business hours
 */
define('BUSINESS_HOURS', [
    'monday' => '10:00-18:00',
    'tuesday' => '10:00-18:00',
    'wednesday' => '10:00-18:00',
    'thursday' => '10:00-18:00',
    'friday' => '10:00-18:00',
    'saturday' => '11:00-16:00',
    'sunday' => 'closed'
]);

/**
 * Contact Information
 */
define('CONTACT_PHONE', '+7 (XXX) XXX-XX-XX');
define('CONTACT_EMAIL', 'info@workshop.com');
define('CONTACT_ADDRESS', 'Москва, ул. Примерная, д. 1');

// ============================================
// SOCIAL MEDIA LINKS
// ============================================

define('SOCIAL_INSTAGRAM', 'https://instagram.com/yourshop');
define('SOCIAL_VKONTAKTE', 'https://vk.com/yourshop');
define('SOCIAL_TELEGRAM', 'https://t.me/yourshop');

?>
