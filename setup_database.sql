-- ============================================
-- Database Setup for Творческая Мастерская
-- ============================================

-- Create database
CREATE DATABASE IF NOT EXISTS workshop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE workshop_db;

-- ============================================
-- Products Table
-- ============================================
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_holiday (holiday),
    INDEX idx_in_stock (in_stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Orders Table
-- ============================================
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Order Items Table
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price INT NOT NULL,
    quantity INT NOT NULL,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Initial Products
-- ============================================
INSERT INTO products (name, description, price, category, holiday, image) VALUES
('Соевая свеча «Уют»', 'Ароматическая свеча из натурального соевого воска с нежным ароматом', 890, 'Свечи', 'Универсальный', ''),
('Набор свечей «Цветочный сад»', 'Набор из трёх свечей в стеклянных баночках с цветочными композициями', 2400, 'Наборы', 'Универсальный', ''),
('Гипсовый поднос с цветами', 'Декоративный поднос из гипса с засушенными цветами', 1800, 'Подносы', 'Универсальный', ''),
('Гипсовая фигурка «Ангел»', 'Декоративная фигурка ангела из гипса ручной работы', 1200, 'Фигурки', 'Универсальный', ''),
('Лавандовое арома саше', 'Ароматическое саше с натуральной лавандой', 490, 'Арома саше', 'Универсальный', ''),
('Новогодняя свеча «Ель»', 'Ароматическая свеча с запахом хвои и мандаринов', 950, 'Свечи', 'Новый год', ''),
('Набор «Весеннее настроение»', 'Набор свечей и арома саше с цветочными ароматами', 2800, 'Наборы', '8 марта', ''),
('Романтический набор', 'Свечи в форме сердец с ароматом розы', 1600, 'Наборы', 'День влюбленных', ''),
('Декоративная свеча «Мрамор»', 'Свеча с эффектом мрамора, станет украшением интерьера', 1100, 'Свечи', 'Универсальный', '');

-- ============================================
-- Create Admin User (optional)
-- ============================================
-- Uncomment if you want to add user authentication
-- CREATE TABLE IF NOT EXISTS users (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     username VARCHAR(100) UNIQUE NOT NULL,
--     password_hash VARCHAR(255) NOT NULL,
--     email VARCHAR(255) UNIQUE NOT NULL,
--     role VARCHAR(50) DEFAULT 'admin',
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     
--     INDEX idx_username (username),
--     INDEX idx_email (email)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- Password hash created with: password_hash('admin123', PASSWORD_DEFAULT)
-- INSERT INTO users (username, password_hash, email, role) VALUES
-- ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@workshop.com', 'admin');

-- ============================================
-- Views for reporting (optional)
-- ============================================

-- View for daily sales
CREATE OR REPLACE VIEW daily_sales AS
SELECT 
    DATE(created_at) as sale_date,
    COUNT(*) as orders_count,
    SUM(total) as total_revenue
FROM orders
GROUP BY DATE(created_at)
ORDER BY sale_date DESC;

-- View for popular products
CREATE OR REPLACE VIEW popular_products AS
SELECT 
    p.id,
    p.name,
    p.category,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity_sold,
    SUM(oi.product_price * oi.quantity) as total_revenue
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id, p.name, p.category
ORDER BY times_ordered DESC;

-- View for orders with details
CREATE OR REPLACE VIEW orders_detailed AS
SELECT 
    o.id as order_id,
    o.name as customer_name,
    o.phone,
    o.email,
    o.address,
    o.total,
    o.status,
    o.created_at,
    GROUP_CONCAT(
        CONCAT(oi.product_name, ' (x', oi.quantity, ')')
        SEPARATOR ', '
    ) as items_list
FROM orders o
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id
ORDER BY o.created_at DESC;

-- ============================================
-- Done!
-- ============================================
SELECT 'Database setup complete!' as message;
