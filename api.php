<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================
// DATABASE CONFIGURATION
// ============================================
// Set USE_MYSQL to true to use MySQL database
// Set to false to use JSON files
define('USE_MYSQL', true);

// MySQL Configuration (only used if USE_MYSQL is true)
define('DB_HOST', '249a5b3670093c61275d8dc4.twc1.net');
define('DB_PORT', '3306');
define('DB_NAME', 'default_db');
define('DB_USER', 'gen_user');
define('DB_PASS', 'PFNdQI1&HW>)s,');
define('DB_CHARSET', 'utf8mb4');

// JSON Files Configuration (only used if USE_MYSQL is false)
define('DB_FILE', 'database.json');
define('ORDERS_FILE', 'orders.json');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB

// ============================================
// DATABASE CONNECTION
// ============================================
$db = null;

if (USE_MYSQL) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $db = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Create tables if they don't exist
        $db->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price INT NOT NULL,
                category VARCHAR(100) NOT NULL,
                holiday VARCHAR(100) NOT NULL,
                image TEXT,
                in_stock BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $db->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(255),
                address TEXT NOT NULL,
                comment TEXT,
                total INT NOT NULL,
                status VARCHAR(50) DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        $db->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_price INT NOT NULL,
                quantity INT NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        // Check if products table is empty and insert initial data
        $stmt = $db->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            insertInitialProducts($db);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit();
    }
}


// ============================================
// HELPER FUNCTIONS FOR JSON
// ============================================
function readDatabase() {
    $data = file_get_contents(DB_FILE);
    return json_decode($data, true);
}

function writeDatabase($data) {
    file_put_contents(DB_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function readOrders() {
    $data = file_get_contents(ORDERS_FILE);
    return json_decode($data, true);
}

function writeOrders($data) {
    file_put_contents(ORDERS_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function generateId($items) {
    if (empty($items)) {
        return 1;
    }
    $maxId = max(array_column($items, 'id'));
    return $maxId + 1;
}

function ensureUploadDir() {
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

function getUploadedImageUrl($file) {
    if (!isset($file) || !is_array($file)) {
        throw new Exception('Файл не передан');
    }

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Ошибка загрузки файла');
    }

    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        throw new Exception('Файл слишком большой (максимум 5MB)');
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new Exception('Некорректный загруженный файл');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif'
    ];

    if (!isset($allowed[$mimeType])) {
        throw new Exception('Поддерживаются только JPG, PNG, WEBP, GIF');
    }

    ensureUploadDir();

    $ext = $allowed[$mimeType];
    $fileName = uniqid('product_', true) . '.' . $ext;
    $targetPath = UPLOAD_DIR . '/' . $fileName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new Exception('Не удалось сохранить файл');
    }

    return './uploads/' . $fileName;
}

// ============================================
// HELPER FUNCTIONS FOR MYSQL
// ============================================
function insertInitialProducts($db) {
    $initialProducts = [
        ['name' => 'Соевая свеча «Уют»', 'description' => 'Ароматическая свеча из натурального соевого воска с нежным ароматом', 'price' => 890, 'category' => 'Свечи', 'holiday' => 'Универсальный', 'image' => ''],
        ['name' => 'Набор свечей «Цветочный сад»', 'description' => 'Набор из трёх свечей в стеклянных баночках с цветочными композициями', 'price' => 2400, 'category' => 'Наборы', 'holiday' => 'Универсальный', 'image' => ''],
        ['name' => 'Гипсовый поднос с цветами', 'description' => 'Декоративный поднос из гипса с засушенными цветами', 'price' => 1800, 'category' => 'Подносы', 'holiday' => 'Универсальный', 'image' => ''],
        ['name' => 'Гипсовая фигурка «Ангел»', 'description' => 'Декоративная фигурка ангела из гипса ручной работы', 'price' => 1200, 'category' => 'Фигурки', 'holiday' => 'Универсальный', 'image' => ''],
        ['name' => 'Лавандовое арома саше', 'description' => 'Ароматическое саше с натуральной лавандой', 'price' => 490, 'category' => 'Арома саше', 'holiday' => 'Универсальный', 'image' => ''],
        ['name' => 'Новогодняя свеча «Ель»', 'description' => 'Ароматическая свеча с запахом хвои и мандаринов', 'price' => 950, 'category' => 'Свечи', 'holiday' => 'Новый год', 'image' => ''],
        ['name' => 'Набор «Весеннее настроение»', 'description' => 'Набор свечей и арома саше с цветочными ароматами', 'price' => 2800, 'category' => 'Наборы', 'holiday' => '8 марта', 'image' => ''],
        ['name' => 'Романтический набор', 'description' => 'Свечи в форме сердец с ароматом розы', 'price' => 1600, 'category' => 'Наборы', 'holiday' => 'День влюбленных', 'image' => ''],
        ['name' => 'Декоративная свеча «Мрамор»', 'description' => 'Свеча с эффектом мрамора, станет украшением интерьера', 'price' => 1100, 'category' => 'Свечи', 'holiday' => 'Универсальный', 'image' => '']
    ];
    
    $stmt = $db->prepare("
        INSERT INTO products (name, description, price, category, holiday, image) 
        VALUES (:name, :description, :price, :category, :holiday, :image)
    ");
    
    foreach ($initialProducts as $product) {
        $stmt->execute($product);
    }
}

// ============================================
// DATABASE ABSTRACTION LAYER
// ============================================
class Database {
    private $db;
    private $useMySQL;
    
    public function __construct($db = null) {
        $this->db = $db;
        $this->useMySQL = USE_MYSQL;
        
        if (!$this->useMySQL) {
            $this->initializeJSONFiles();
        }
    }
    
    private function initializeJSONFiles() {
        // Initialize database.json if it doesn't exist
        if (!file_exists(DB_FILE)) {
            $initialData = [
                'products' => [
                    ['id' => 1, 'name' => 'Соевая свеча «Уют»', 'description' => 'Ароматическая свеча из натурального соевого воска с нежным ароматом', 'price' => 890, 'category' => 'Свечи', 'holiday' => 'Универсальный', 'image' => '', 'in_stock' => true],
                    ['id' => 2, 'name' => 'Набор свечей «Цветочный сад»', 'description' => 'Набор из трёх свечей в стеклянных баночках с цветочными композициями', 'price' => 2400, 'category' => 'Наборы', 'holiday' => 'Универсальный', 'image' => '', 'in_stock' => true],
                    ['id' => 3, 'name' => 'Гипсовый поднос с цветами', 'description' => 'Декоративный поднос из гипса с засушенными цветами', 'price' => 1800, 'category' => 'Подносы', 'holiday' => 'Универсальный', 'image' => '', 'in_stock' => true],
                    ['id' => 4, 'name' => 'Гипсовая фигурка «Ангел»', 'description' => 'Декоративная фигурка ангела из гипса ручной работы', 'price' => 1200, 'category' => 'Фигурки', 'holiday' => 'Универсальный', 'image' => '', 'in_stock' => true],
                    ['id' => 5, 'name' => 'Лавандовое арома саше', 'description' => 'Ароматическое саше с натуральной лавандой', 'price' => 490, 'category' => 'Арома саше', 'holiday' => 'Универсальный', 'image' => '', 'in_stock' => true],
                    ['id' => 6, 'name' => 'Новогодняя свеча «Ель»', 'description' => 'Ароматическая свеча с запахом хвои и мандаринов', 'price' => 950, 'category' => 'Свечи', 'holiday' => 'Новый год', 'image' => '', 'in_stock' => true],
                    ['id' => 7, 'name' => 'Набор «Весеннее настроение»', 'description' => 'Набор свечей и арома саше с цветочными ароматами', 'price' => 2800, 'category' => 'Наборы', 'holiday' => '8 марта', 'image' => '', 'in_stock' => true],
                    ['id' => 8, 'name' => 'Романтический набор', 'description' => 'Свечи в форме сердец с ароматом розы', 'price' => 1600, 'category' => 'Наборы', 'holiday' => 'День влюбленных', 'image' => '', 'in_stock' => true],
                    ['id' => 9, 'name' => 'Декоративная свеча «Мрамор»', 'description' => 'Свеча с эффектом мрамора, станет украшением интерьера', 'price' => 1100, 'category' => 'Свечи', 'holiday' => 'Универсальный', 'image' => '', 'in_stock' => true]
                ]
            ];
            file_put_contents(DB_FILE, json_encode($initialData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
        
        // Initialize orders.json if it doesn't exist
        if (!file_exists(ORDERS_FILE)) {
            file_put_contents(ORDERS_FILE, json_encode(['orders' => []], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }
    
    // PRODUCTS
    public function getProducts() {
        if ($this->useMySQL) {
            $stmt = $this->db->query("SELECT * FROM products ORDER BY id DESC");
            return $stmt->fetchAll();
        } else {
            $data = readDatabase();
            return $data['products'];
        }
    }
    
    public function addProduct($productData) {
        if ($this->useMySQL) {
            $stmt = $this->db->prepare("
                INSERT INTO products (name, description, price, category, holiday, image) 
                VALUES (:name, :description, :price, :category, :holiday, :image)
            ");
            
            $stmt->execute([
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => (int)$productData['price'],
                'category' => $productData['category'],
                'holiday' => $productData['holiday'],
                'image' => $productData['image']
            ]);
            
            $productData['id'] = $this->db->lastInsertId();
            $productData['in_stock'] = true;
            return $productData;
        } else {
            $data = readDatabase();
            
            $newProduct = [
                'id' => generateId($data['products']),
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => (int)$productData['price'],
                'category' => $productData['category'],
                'holiday' => $productData['holiday'],
                'image' => $productData['image'],
                'in_stock' => true
            ];
            
            $data['products'][] = $newProduct;
            writeDatabase($data);
            return $newProduct;
        }
    }
    
    public function updateProduct($productData) {
        if ($this->useMySQL) {
            $stmt = $this->db->prepare("
                UPDATE products 
                SET name = :name, 
                    description = :description, 
                    price = :price, 
                    category = :category, 
                    holiday = :holiday, 
                    image = :image,
                    in_stock = :in_stock
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $productData['id'],
                'name' => $productData['name'],
                'description' => $productData['description'],
                'price' => (int)$productData['price'],
                'category' => $productData['category'],
                'holiday' => $productData['holiday'],
                'image' => $productData['image'],
                'in_stock' => $productData['in_stock'] ?? true
            ]);
        } else {
            $data = readDatabase();
            $productId = $productData['id'];
            $index = array_search($productId, array_column($data['products'], 'id'));
            
            if ($index !== false) {
                $data['products'][$index] = [
                    'id' => $productId,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => (int)$productData['price'],
                    'category' => $productData['category'],
                    'holiday' => $productData['holiday'],
                    'image' => $productData['image'],
                    'in_stock' => $productData['in_stock'] ?? true
                ];
                
                writeDatabase($data);
                return true;
            }
            return false;
        }
    }
    
    public function deleteProduct($productId) {
        if ($this->useMySQL) {
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
            return $stmt->execute(['id' => $productId]);
        } else {
            $data = readDatabase();
            $data['products'] = array_values(array_filter($data['products'], function($p) use ($productId) {
                return $p['id'] !== $productId;
            }));
            writeDatabase($data);
            return true;
        }
    }
    
    // ORDERS
    public function getOrders() {
        if ($this->useMySQL) {
            $stmt = $this->db->query("
                SELECT o.*, 
                    GROUP_CONCAT(
                        CONCAT(oi.product_name, '|', oi.product_price, '|', oi.quantity) 
                        SEPARATOR ';;'
                    ) as items_data
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                GROUP BY o.id
                ORDER BY o.id DESC
            ");
            
            $orders = $stmt->fetchAll();
            
            // Parse items data
            foreach ($orders as &$order) {
                $items = [];
                if ($order['items_data']) {
                    $itemsArray = explode(';;', $order['items_data']);
                    foreach ($itemsArray as $item) {
                        list($name, $price, $quantity) = explode('|', $item);
                        $items[] = [
                            'name' => $name,
                            'price' => (int)$price,
                            'quantity' => (int)$quantity
                        ];
                    }
                }
                $order['items'] = $items;
                unset($order['items_data']);
            }
            
            return $orders;
        } else {
            $data = readOrders();
            return $data['orders'];
        }
    }
    
    public function createOrder($orderData) {
        if ($this->useMySQL) {
            $this->db->beginTransaction();
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO orders (name, phone, email, address, comment, total, status) 
                    VALUES (:name, :phone, :email, :address, :comment, :total, :status)
                ");
                
                $stmt->execute([
                    'name' => $orderData['name'],
                    'phone' => $orderData['phone'],
                    'email' => $orderData['email'],
                    'address' => $orderData['address'],
                    'comment' => $orderData['comment'],
                    'total' => (int)$orderData['total'],
                    'status' => 'new'
                ]);
                
                $orderId = $this->db->lastInsertId();
                
                // Insert order items
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity) 
                    VALUES (:order_id, :product_id, :product_name, :product_price, :quantity)
                ");
                
                foreach ($orderData['items'] as $item) {
                    $stmt->execute([
                        'order_id' => $orderId,
                        'product_id' => $item['id'],
                        'product_name' => $item['name'],
                        'product_price' => (int)$item['price'],
                        'quantity' => (int)$item['quantity']
                    ]);
                }
                
                $this->db->commit();
                
                $orderData['id'] = $orderId;
                return $orderData;
                
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        } else {
            $orders = readOrders();
            
            $newOrder = [
                'id' => generateId($orders['orders']),
                'name' => $orderData['name'],
                'phone' => $orderData['phone'],
                'email' => $orderData['email'],
                'address' => $orderData['address'],
                'comment' => $orderData['comment'],
                'items' => $orderData['items'],
                'total' => $orderData['total'],
                'date' => $orderData['date'] ?? date('c'),
                'status' => 'new'
            ];
            
            $orders['orders'][] = $newOrder;
            writeOrders($orders);
            return $newOrder;
        }
    }
    
    public function updateOrderStatus($orderId, $status) {
        if ($this->useMySQL) {
            $stmt = $this->db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            return $stmt->execute(['id' => $orderId, 'status' => $status]);
        } else {
            $orders = readOrders();
            $index = array_search($orderId, array_column($orders['orders'], 'id'));
            
            if ($index !== false) {
                $orders['orders'][$index]['status'] = $status;
                writeOrders($orders);
                return true;
            }
            return false;
        }
    }
}

// Initialize database layer
$database = new Database($db);

// ============================================
// API ROUTES
// ============================================
// Get action from query string
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getProducts':
            $products = $database->getProducts();
            echo json_encode($products, JSON_UNESCAPED_UNICODE);
            break;

        case 'addProduct':
            $input = json_decode(file_get_contents('php://input'), true);
            $product = $database->addProduct($input);
            echo json_encode(['success' => true, 'product' => $product], JSON_UNESCAPED_UNICODE);
            break;

        case 'updateProduct':
            $input = json_decode(file_get_contents('php://input'), true);
            $success = $database->updateProduct($input);
            
            if ($success) {
                echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found'], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'deleteProduct':
            $productId = (int)$_GET['id'];
            $success = $database->deleteProduct($productId);
            echo json_encode(['success' => $success], JSON_UNESCAPED_UNICODE);
            break;

        case 'createOrder':
            $input = json_decode(file_get_contents('php://input'), true);
            $order = $database->createOrder($input);
            
            // Here you could add email notification functionality
            // mail($order['email'], 'Заказ получен', '...');
            
            echo json_encode(['success' => true, 'order' => $order], JSON_UNESCAPED_UNICODE);
            break;

        case 'uploadImage':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
                break;
            }

            $imageUrl = getUploadedImageUrl($_FILES['image'] ?? null);
            echo json_encode(['success' => true, 'image' => $imageUrl], JSON_UNESCAPED_UNICODE);
            break;

        case 'getOrders':
            $orders = $database->getOrders();
            echo json_encode($orders, JSON_UNESCAPED_UNICODE);
            break;

        case 'updateOrderStatus':
            $orderId = (int)$_GET['id'];
            $newStatus = $_GET['status'];
            $success = $database->updateOrderStatus($orderId, $newStatus);
            
            if ($success) {
                echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found'], JSON_UNESCAPED_UNICODE);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action'], JSON_UNESCAPED_UNICODE);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
