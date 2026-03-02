<?php
/**
 * Migration Script: JSON to MySQL
 * 
 * This script migrates data from JSON files (database.json and orders.json)
 * to MySQL database.
 * 
 * Usage: php migrate_json_to_mysql.php
 */

// Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'workshop_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('DB_FILE', 'database.json');
define('ORDERS_FILE', 'orders.json');

echo "===========================================\n";
echo "Migration: JSON to MySQL\n";
echo "===========================================\n\n";

// Check if JSON files exist
if (!file_exists(DB_FILE)) {
    die("Error: database.json not found!\n");
}

if (!file_exists(ORDERS_FILE)) {
    die("Error: orders.json not found!\n");
}

// Connect to MySQL
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "✓ Connected to MySQL database\n\n";
} catch (PDOException $e) {
    die("Error: Could not connect to MySQL: " . $e->getMessage() . "\n");
}

// Read JSON data
$jsonData = json_decode(file_get_contents(DB_FILE), true);
$ordersData = json_decode(file_get_contents(ORDERS_FILE), true);

if (!$jsonData || !isset($jsonData['products'])) {
    die("Error: Invalid database.json format\n");
}

if (!$ordersData || !isset($ordersData['orders'])) {
    die("Error: Invalid orders.json format\n");
}

$products = $jsonData['products'];
$orders = $ordersData['orders'];

echo "Found " . count($products) . " products to migrate\n";
echo "Found " . count($orders) . " orders to migrate\n\n";

// Ask for confirmation
echo "This will:\n";
echo "1. Delete all existing data in MySQL\n";
echo "2. Import data from JSON files\n\n";
echo "Continue? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) != 'yes') {
    die("Migration cancelled.\n");
}

echo "\nStarting migration...\n\n";

try {
    $pdo->beginTransaction();
    
    // Clear existing data
    echo "Clearing existing data...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE order_items");
    $pdo->exec("TRUNCATE TABLE orders");
    $pdo->exec("TRUNCATE TABLE products");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✓ Existing data cleared\n\n";
    
    // Migrate products
    echo "Migrating products...\n";
    $stmt = $pdo->prepare("
        INSERT INTO products (id, name, description, price, category, holiday, image, in_stock) 
        VALUES (:id, :name, :description, :price, :category, :holiday, :image, :in_stock)
    ");
    
    $migratedProducts = 0;
    foreach ($products as $product) {
        $stmt->execute([
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'category' => $product['category'],
            'holiday' => $product['holiday'],
            'image' => $product['image'] ?? '',
            'in_stock' => $product['in_stock'] ?? true
        ]);
        $migratedProducts++;
        echo "  - Migrated: " . $product['name'] . "\n";
    }
    echo "✓ Migrated $migratedProducts products\n\n";
    
    // Update auto_increment
    $maxId = max(array_column($products, 'id'));
    $pdo->exec("ALTER TABLE products AUTO_INCREMENT = " . ($maxId + 1));
    
    // Migrate orders
    if (count($orders) > 0) {
        echo "Migrating orders...\n";
        
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (id, name, phone, email, address, comment, total, status, created_at) 
            VALUES (:id, :name, :phone, :email, :address, :comment, :total, :status, :created_at)
        ");
        
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity) 
            VALUES (:order_id, :product_id, :product_name, :product_price, :quantity)
        ");
        
        $migratedOrders = 0;
        $migratedItems = 0;
        
        foreach ($orders as $order) {
            // Insert order
            $orderStmt->execute([
                'id' => $order['id'],
                'name' => $order['name'],
                'phone' => $order['phone'],
                'email' => $order['email'] ?? '',
                'address' => $order['address'],
                'comment' => $order['comment'] ?? '',
                'total' => $order['total'],
                'status' => $order['status'] ?? 'new',
                'created_at' => $order['date'] ?? date('Y-m-d H:i:s')
            ]);
            
            // Insert order items
            if (isset($order['items']) && is_array($order['items'])) {
                foreach ($order['items'] as $item) {
                    $itemStmt->execute([
                        'order_id' => $order['id'],
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'product_price' => $item['price'],
                        'quantity' => $item['quantity']
                    ]);
                    $migratedItems++;
                }
            }
            
            $migratedOrders++;
            echo "  - Migrated order #" . $order['id'] . " from " . $order['name'] . "\n";
        }
        
        echo "✓ Migrated $migratedOrders orders with $migratedItems items\n\n";
        
        // Update auto_increment for orders
        $maxOrderId = max(array_column($orders, 'id'));
        $pdo->exec("ALTER TABLE orders AUTO_INCREMENT = " . ($maxOrderId + 1));
    }
    
    $pdo->commit();
    
    echo "\n===========================================\n";
    echo "Migration completed successfully!\n";
    echo "===========================================\n\n";
    
    echo "Summary:\n";
    echo "- Products migrated: $migratedProducts\n";
    echo "- Orders migrated: $migratedOrders\n";
    echo "- Order items migrated: $migratedItems\n\n";
    
    echo "Next steps:\n";
    echo "1. Update api.php: Set USE_MYSQL to true\n";
    echo "2. Update database credentials in api.php\n";
    echo "3. Test the application\n";
    echo "4. Backup your JSON files as they won't be used anymore\n\n";
    
    // Create backup of JSON files
    $backupDir = 'json_backup_' . date('Y-m-d_H-i-s');
    mkdir($backupDir);
    copy(DB_FILE, $backupDir . '/' . DB_FILE);
    copy(ORDERS_FILE, $backupDir . '/' . ORDERS_FILE);
    echo "✓ JSON files backed up to: $backupDir/\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n✗ Error during migration: " . $e->getMessage() . "\n";
    echo "Migration rolled back. No changes were made.\n";
    exit(1);
}

echo "\nDone!\n";
?>
