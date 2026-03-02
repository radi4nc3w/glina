# Творческая Мастерская - Интернет-магазин свечей ручной работы

Полнофункциональное веб-приложение для продажи свечей и декора ручной работы с админ-панелью.

## Возможности

### Для покупателей:
- ✨ Красивый современный дизайн
- 🛍️ Каталог товаров с фильтрацией
- 🔍 Фильтрация по категориям (Свечи, Наборы, Арома саше, Фигурки, Подносы, Декор)
- 🎉 Фильтрация по праздникам (Универсальный, Новый год, 8 марта, День влюбленных, День рождения, Свадьба)
- 🛒 Корзина с управлением количеством товаров
- 📝 Форма заказа (без оплаты, для связи с клиентом)
- 💾 Автоматическое сохранение корзины в LocalStorage

### Для администратора:
- 📦 Управление товарами (добавление, редактирование, удаление)
- 📊 Просмотр списка товаров
- 🎨 Настройка категорий и праздников
- 💰 Установка цен
- 🖼️ Добавление изображений товаров (по URL)

## Установка

### Требования:
- PHP 7.0 или выше
- Веб-сервер (Apache, Nginx) или PHP встроенный сервер
- Браузер с поддержкой современного JavaScript
- MySQL 5.7+ или MariaDB 10.2+ (опционально, для продакшена)

### Выбор базы данных:

Приложение поддерживает два типа хранения данных:

1. **JSON-файлы** (по умолчанию) - для разработки и небольших проектов
   - Не требует установки MySQL
   - Простая настройка
   - Автоматическое создание файлов

2. **MySQL** - для продакшена и больших проектов
   - Лучшая производительность
   - Поддержка транзакций
   - Масштабируемость

### Шаги установки:

#### Вариант А: Использование JSON-файлов (быстрый старт)

1. **Скопируйте файлы на сервер:**
   ```bash
   # Скопируйте все файлы в папку вашего веб-сервера
   index.html
   api.php
   ```

2. **Настройте права доступа:**
   ```bash
   # API должен иметь права на запись для создания файлов базы данных
   chmod 755 api.php
   chmod 777 .  # Папка должна быть доступна для записи
   ```

3. **Убедитесь что USE_MYSQL = false в api.php** (по умолчанию)
   ```php
   define('USE_MYSQL', false);
   ```

4. **Запустите приложение:**
   ```bash
   php -S localhost:8000
   ```
   Откройте в браузере: http://localhost:8000

#### Вариант Б: Использование MySQL (рекомендуется для продакшена)

1. **Создайте базу данных MySQL:**
   ```bash
   # Войдите в MySQL
   mysql -u root -p
   
   # Выполните SQL-скрипт
   source setup_database.sql
   
   # Или создайте вручную
   CREATE DATABASE workshop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Или импортируйте файл через phpMyAdmin:
   - Откройте phpMyAdmin
   - Создайте новую базу данных `workshop_db`
   - Выберите вкладку "Импорт"
   - Выберите файл `setup_database.sql`
   - Нажмите "Выполнить"

2. **Настройте подключение к базе данных в api.php:**
   ```php
   // Измените эти настройки в начале файла api.php
   define('USE_MYSQL', true);  // Включаем MySQL
   
   define('DB_HOST', 'localhost');        // Хост БД
   define('DB_NAME', 'workshop_db');      // Имя БД
   define('DB_USER', 'root');             // Пользователь БД
   define('DB_PASS', 'your_password');    // Пароль БД
   define('DB_CHARSET', 'utf8mb4');
   ```

3. **Убедитесь, что расширение PDO MySQL установлено:**
   ```bash
   # Проверьте установку
   php -m | grep pdo_mysql
   
   # Если не установлено (Ubuntu/Debian)
   sudo apt-get install php-mysql
   
   # Если не установлено (CentOS/RHEL)
   sudo yum install php-mysqlnd
   ```

4. **Запустите приложение:**
   ```bash
   php -S localhost:8000
   ```

### Миграция с JSON на MySQL:

Если вы начали с JSON и хотите перейти на MySQL:

1. Создайте базу данных и выполните `setup_database.sql`
2. Экспортируйте данные из JSON:
   ```bash
   # Используйте скрипт миграции (создайте его отдельно)
   php migrate_json_to_mysql.php
   ```
3. Измените `USE_MYSQL` на `true` в `api.php`
4. Протестируйте работу приложения

## Использование

### Для покупателей:

1. **Просмотр каталога:**
   - Откройте главную страницу
   - Прокрутите до раздела "Каталог"
   - Используйте фильтры для поиска нужных товаров

2. **Добавление в корзину:**
   - Нажмите кнопку "В корзину" на карточке товара
   - Товар добавится в корзину (количество показано в шапке)

3. **Оформление заказа:**
   - Нажмите кнопку "Корзина" в шапке сайта
   - Проверьте товары, измените количество если нужно
   - Нажмите "Оформить заказ"
   - Заполните форму с контактными данными
   - Нажмите "Отправить заказ"

### Для администратора:

1. **Вход в админ-панель:**
   - Нажмите "Админ" в шапке сайта
   - Откроется панель управления

2. **Добавление товара:**
   - Нажмите "+ Добавить товар"
   - Заполните форму:
     - Название
     - Описание
     - Цена
     - Категория
     - Праздник
     - URL изображения (опционально)
   - Нажмите "Сохранить"

3. **Редактирование товара:**
   - Найдите товар в списке
   - Нажмите "Редактировать"
   - Внесите изменения
   - Нажмите "Сохранить"

4. **Удаление товара:**
   - Найдите товар в списке
   - Нажмите "Удалить"
   - Подтвердите удаление

## Структура файлов

```
project/
├── index.html              # Главная страница приложения (React)
├── api.php                 # Backend API для работы с данными
├── config.example.php      # Пример конфигурационного файла
├── setup_database.sql      # SQL-скрипт для создания таблиц MySQL
├── database.json           # База данных товаров (создается автоматически при USE_MYSQL=false)
├── orders.json             # База данных заказов (создается автоматически при USE_MYSQL=false)
└── README.md               # Этот файл
```

## Решение проблем

### Проблемы с базой данных JSON

**Ошибка: "Permission denied" при создании файлов**
```bash
# Дайте права на запись
chmod 777 /path/to/project
```

**Файлы database.json или orders.json повреждены**
```bash
# Удалите файлы и перезапустите приложение
rm database.json orders.json
# Файлы будут созданы заново при первом запросе
```

### Проблемы с MySQL

**Ошибка: "SQLSTATE[HY000] [2002] Connection refused"**
- Проверьте, запущен ли MySQL: `sudo systemctl status mysql`
- Проверьте настройки подключения в `api.php`

**Ошибка: "Access denied for user"**
- Проверьте логин и пароль в `api.php`
- Убедитесь, что пользователь имеет права на базу данных:
  ```sql
  GRANT ALL PRIVILEGES ON workshop_db.* TO 'your_user'@'localhost';
  FLUSH PRIVILEGES;
  ```

**Ошибка: "Unknown database 'workshop_db'"**
- База данных не создана. Выполните `setup_database.sql`

**Таблицы не создаются автоматически**
```bash
# Выполните SQL-скрипт вручную
mysql -u root -p workshop_db < setup_database.sql
```

**Проверка подключения к MySQL:**
```php
// Создайте файл test_db.php
<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=workshop_db', 'root', 'password');
    echo "Подключение успешно!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
```

### Общие проблемы

**Не отображаются товары**
- Проверьте консоль браузера на ошибки (F12)
- Проверьте, что API возвращает данные: `http://localhost:8000/api.php?action=getProducts`
- Убедитесь, что в базе есть товары

**Товары не добавляются в корзину**
- Проверьте LocalStorage в браузере (F12 → Application → Local Storage)
- Очистите кеш браузера

**Заказы не сохраняются**
- Проверьте права доступа к файлам (для JSON)
- Проверьте подключение к MySQL (для MySQL)
- Проверьте логи ошибок PHP: `tail -f /var/log/php_errors.log`

### Производительность

**Медленная работа с JSON-файлами при большом количестве товаров**
- Перейдите на MySQL
- Настройте индексы в базе данных

**Медленные запросы MySQL**
```sql
-- Проверьте медленные запросы
SHOW PROCESSLIST;

-- Добавьте индексы
CREATE INDEX idx_category_holiday ON products(category, holiday);
```

## API Endpoints

### Товары:
- `GET api.php?action=getProducts` - Получить все товары
- `POST api.php?action=addProduct` - Добавить товар
- `POST api.php?action=updateProduct` - Обновить товар
- `DELETE api.php?action=deleteProduct&id={id}` - Удалить товар

### Заказы:
- `POST api.php?action=createOrder` - Создать заказ
- `GET api.php?action=getOrders` - Получить все заказы
- `GET api.php?action=updateOrderStatus&id={id}&status={status}` - Обновить статус заказа

## База данных

Приложение поддерживает два типа хранения данных:

### JSON-файлы (по умолчанию)

При использовании JSON создаются два файла:

#### database.json - структура товара:
```json
{
  "products": [
    {
      "id": 1,
      "name": "Название товара",
      "description": "Описание",
      "price": 890,
      "category": "Свечи",
      "holiday": "Универсальный",
      "image": "https://...",
      "in_stock": true
    }
  ]
}
```

#### orders.json - структура заказа:
```json
{
  "orders": [
    {
      "id": 1,
      "name": "Имя клиента",
      "phone": "+7...",
      "email": "email@example.com",
      "address": "Адрес доставки",
      "comment": "Комментарий",
      "items": [...],
      "total": 2400,
      "date": "2024-03-02T10:30:00Z",
      "status": "new"
    }
  ]
}
```

### MySQL Database

При использовании MySQL создаются три таблицы:

#### Таблица products:
```sql
CREATE TABLE products (
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
);
```

#### Таблица orders:
```sql
CREATE TABLE orders (
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
);
```

#### Таблица order_items:
```sql
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

### Полезные SQL запросы

**Получить статистику продаж:**
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as orders_count,
    SUM(total) as revenue
FROM orders
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

**Топ продуктов:**
```sql
SELECT 
    p.name,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_sold
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id
ORDER BY times_ordered DESC
LIMIT 10;
```

**Заказы за последние 7 дней:**
```sql
SELECT * FROM orders
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

## Настройка

### Изменение цветовой схемы:
Откройте `index.html` и измените CSS-переменные в `:root`:

```css
:root {
    --cream: #F5F1EC;
    --rose-blush: #E8B4B8;
    --dusty-rose: #D4989C;
    --sage: #A8B5A1;
    --charcoal: #3C3C3C;
    --warm-white: #FDFBF7;
    --terracotta: #C17A6F;
}
```

### Добавление категорий:
В функции `ProductFormModal` найдите блок с категориями и добавьте новые опции:

```javascript
<select className="form-input" ...>
    <option>Свечи</option>
    <option>Наборы</option>
    <option>Ваша новая категория</option>
</select>
```

### Добавление праздников:
Аналогично категориям, добавьте новые праздники в соответствующем select.

## Улучшения для продакшена

Для использования в реальном проекте рекомендуется:

1. **База данных:**
   - Перейти с JSON на MySQL/PostgreSQL
   - Добавить индексы для быстрого поиска

2. **Безопасность:**
   - Добавить авторизацию для админ-панели
   - Использовать HTTPS
   - Добавить CSRF-защиту
   - Валидация и санитизация данных

3. **Загрузка изображений:**
   - Добавить возможность загрузки файлов напрямую
   - Оптимизация изображений
   - CDN для статики

4. **Уведомления:**
   - Email-уведомления о новых заказах
   - SMS-уведомления клиентам
   - Telegram-бот для администратора

5. **Оплата:**
   - Интеграция платежных систем (ЮKassa, Stripe, PayPal)
   - Система подтверждения оплаты

6. **SEO:**
   - Добавить мета-теги
   - Создать sitemap.xml
   - Настроить robots.txt

## Поддержка браузеров

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Лицензия

Проект создан для демонстрационных целей.

## Контакты

Если у вас возникли вопросы, свяжитесь с разработчиком.

---

Создано с ❤️ для Творческой Мастерской
