<?php
/**
 * Comprehensive PHP Example Script
 * Approximately 1000 lines demonstrating various PHP features
 */

// ==================== Configuration Section ====================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('APP_NAME', 'PHP Demo Application');
define('APP_VERSION', '1.0.0');
define('MAX_LOGIN_ATTEMPTS', 5);
define('TEMP_DIR', __DIR__ . '/temp/');

// ==================== Utility Functions ====================

/**
 * Sanitize user input
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate a random string
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Log message to file
 * @param string $message
 * @param string $type
 */
function logMessage($message, $type = 'INFO') {
    $logFile = TEMP_DIR . 'application.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// ==================== Database Class ====================

class Database {
    private $connection;
    private $host;
    private $username;
    private $password;
    private $database;
    
    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database}",
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logMessage("Database connection failed: " . $e->getMessage(), 'ERROR');
            throw new Exception("Database connection error");
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            logMessage("Query failed: " . $e->getMessage() . " SQL: $sql", 'ERROR');
            throw new Exception("Database query error");
        }
    }
    
    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function __destruct() {
        $this->connection = null;
    }
}

// ==================== User Class ====================

class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $role;
    private $createdAt;
    private $updatedAt;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        $stmt = $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
        $userData = $stmt->fetch();
        
        if ($userData) {
            $this->id = $userData['id'];
            $this->username = $userData['username'];
            $this->email = $userData['email'];
            $this->role = $userData['role'];
            $this->createdAt = $userData['created_at'];
            $this->updatedAt = $userData['updated_at'];
            return true;
        }
        
        return false;
    }
    
    public function create($username, $email, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->db->query(
            "INSERT INTO users (username, email, password, role, created_at, updated_at) 
             VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$username, $email, $hashedPassword, $role]
        );
        
        $this->id = $this->db->getLastInsertId();
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        
        return $this->id;
    }
    
    public function update($data) {
        $updates = [];
        $params = [];
        
        if (isset($data['username'])) {
            $updates[] = 'username = ?';
            $params[] = $data['username'];
            $this->username = $data['username'];
        }
        
        if (isset($data['email'])) {
            $updates[] = 'email = ?';
            $params[] = $data['email'];
            $this->email = $data['email'];
        }
        
        if (isset($data['password'])) {
            $updates[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        if (isset($data['role'])) {
            $updates[] = 'role = ?';
            $params[] = $data['role'];
            $this->role = $data['role'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = 'updated_at = NOW()';
        $params[] = $this->id;
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $this->db->query($sql, $params);
        
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }
    
    public function delete() {
        $this->db->query("DELETE FROM users WHERE id = ?", [$this->id]);
        $this->id = null;
        return true;
    }
    
    public function authenticate($password) {
        $stmt = $this->db->query("SELECT password FROM users WHERE id = ?", [$this->id]);
        $userData = $stmt->fetch();
        
        if ($userData && password_verify($password, $userData['password'])) {
            return true;
        }
        
        return false;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
}

// ==================== Product Class ====================

class Product {
    private $db;
    private $id;
    private $name;
    private $description;
    private $price;
    private $stock;
    private $category;
    private $createdAt;
    private $updatedAt;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        $stmt = $this->db->query("SELECT * FROM products WHERE id = ?", [$id]);
        $productData = $stmt->fetch();
        
        if ($productData) {
            $this->id = $productData['id'];
            $this->name = $productData['name'];
            $this->description = $productData['description'];
            $this->price = $productData['price'];
            $this->stock = $productData['stock'];
            $this->category = $productData['category'];
            $this->createdAt = $productData['created_at'];
            $this->updatedAt = $productData['updated_at'];
            return true;
        }
        
        return false;
    }
    
    public function create($name, $description, $price, $stock, $category) {
        $this->db->query(
            "INSERT INTO products (name, description, price, stock, category, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
            [$name, $description, $price, $stock, $category]
        );
        
        $this->id = $this->db->getLastInsertId();
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->category = $category;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        
        return $this->id;
    }
    
    public function update($data) {
        $updates = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updates[] = 'name = ?';
            $params[] = $data['name'];
            $this->name = $data['name'];
        }
        
        if (isset($data['description'])) {
            $updates[] = 'description = ?';
            $params[] = $data['description'];
            $this->description = $data['description'];
        }
        
        if (isset($data['price'])) {
            $updates[] = 'price = ?';
            $params[] = $data['price'];
            $this->price = $data['price'];
        }
        
        if (isset($data['stock'])) {
            $updates[] = 'stock = ?';
            $params[] = $data['stock'];
            $this->stock = $data['stock'];
        }
        
        if (isset($data['category'])) {
            $updates[] = 'category = ?';
            $params[] = $data['category'];
            $this->category = $data['category'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = 'updated_at = NOW()';
        $params[] = $this->id;
        
        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
        $this->db->query($sql, $params);
        
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }
    
    public function delete() {
        $this->db->query("DELETE FROM products WHERE id = ?", [$this->id]);
        $this->id = null;
        return true;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
    public function getStock() { return $this->stock; }
    public function getCategory() { return $this->category; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    
    // Business logic methods
    public function isInStock() {
        return $this->stock > 0;
    }
    
    public function applyDiscount($percent) {
        if ($percent <= 0 || $percent > 100) {
            throw new InvalidArgumentException("Discount percentage must be between 1 and 100");
        }
        
        $this->price = $this->price * (1 - ($percent / 100));
        $this->update(['price' => $this->price]);
    }
}

// ==================== Order Class ====================

class Order {
    private $db;
    private $id;
    private $userId;
    private $status;
    private $total;
    private $items = [];
    private $createdAt;
    private $updatedAt;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        $stmt = $this->db->query("SELECT * FROM orders WHERE id = ?", [$id]);
        $orderData = $stmt->fetch();
        
        if ($orderData) {
            $this->id = $orderData['id'];
            $this->userId = $orderData['user_id'];
            $this->status = $orderData['status'];
            $this->total = $orderData['total'];
            $this->createdAt = $orderData['created_at'];
            $this->updatedAt = $orderData['updated_at'];
            
            // Load order items
            $stmt = $this->db->query("SELECT * FROM order_items WHERE order_id = ?", [$this->id]);
            $this->items = $stmt->fetchAll();
            
            return true;
        }
        
        return false;
    }
    
    public function create($userId, $items) {
        $this->db->beginTransaction();
        
        try {
            // Create order
            $this->db->query(
                "INSERT INTO orders (user_id, status, total, created_at, updated_at) 
                 VALUES (?, 'pending', 0, NOW(), NOW())",
                [$userId]
            );
            
            $this->id = $this->db->getLastInsertId();
            $this->userId = $userId;
            $this->status = 'pending';
            $this->createdAt = date('Y-m-d H:i:s');
            $this->updatedAt = date('Y-m-d H:i:s');
            
            // Add items and calculate total
            $total = 0;
            foreach ($items as $item) {
                // Get product price
                $productStmt = $this->db->query("SELECT price FROM products WHERE id = ?", [$item['product_id']]);
                $product = $productStmt->fetch();
                
                if (!$product) {
                    throw new Exception("Product not found: " . $item['product_id']);
                }
                
                $itemTotal = $product['price'] * $item['quantity'];
                $total += $itemTotal;
                
                $this->db->query(
                    "INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                     VALUES (?, ?, ?, ?, ?)",
                    [$this->id, $item['product_id'], $item['quantity'], $product['price'], $itemTotal]
                );
                
                $this->items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                    'total' => $itemTotal
                ];
            }
            
            // Update order total
            $this->db->query("UPDATE orders SET total = ? WHERE id = ?", [$total, $this->id]);
            $this->total = $total;
            
            $this->db->commit();
            return $this->id;
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Order creation failed: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    public function updateStatus($status) {
        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new InvalidArgumentException("Invalid order status");
        }
        
        $this->db->query(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $this->id]
        );
        
        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->userId; }
    public function getStatus() { return $this->status; }
    public function getTotal() { return $this->total; }
    public function getItems() { return $this->items; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
}

// ==================== File Uploader Class ====================

class FileUploader {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private $maxSize = 2097152; // 2MB
    
    public function __construct($uploadDir) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function upload($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file['error']);
        }
        
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception("File type not allowed: " . $file['type']);
        }
        
        if ($file['size'] > $this->maxSize) {
            throw new Exception("File size exceeds maximum allowed size");
        }
        
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $file['name']);
        $destination = $this->uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Failed to move uploaded file");
        }
        
        return [
            'original_name' => $file['name'],
            'stored_name' => $filename,
            'path' => $destination,
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }
    
    public function delete($filename) {
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}

// ==================== API Response Class ====================

class ApiResponse {
    private $status;
    private $message;
    private $data;
    private $httpCode;
    
    public function __construct($status = 'success', $message = '', $data = [], $httpCode = 200) {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->httpCode = $httpCode;
    }
    
    public function send() {
        http_response_code($this->httpCode);
        header('Content-Type: application/json');
        
        echo json_encode([
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => time()
        ]);
        
        exit;
    }
    
    public static function success($data = [], $message = 'Operation successful') {
        $response = new self('success', $message, $data, 200);
        $response->send();
    }
    
    public static function error($message = 'An error occurred', $httpCode = 400, $data = []) {
        $response = new self('error', $message, $data, $httpCode);
        $response->send();
    }
    
    public static function notFound($message = 'Resource not found') {
        $response = new self('error', $message, [], 404);
        $response->send();
    }
}

// ==================== Authentication Middleware ====================

class AuthMiddleware {
    private $db;
    private $user;
    
    public function __construct(Database $db) {
        $this->db = $db;
        $this->user = null;
    }
    
    public function authenticate($username, $password) {
        $stmt = $this->db->query("SELECT * FROM users WHERE username = ?", [$username]);
        $userData = $stmt->fetch();
        
        if (!$userData) {
            return false;
        }
        
        if (!password_verify($password, $userData['password'])) {
            return false;
        }
        
        $this->user = $userData;
        return true;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function requireAuth() {
        if (!$this->user) {
            ApiResponse::error('Authentication required', 401);
        }
    }
    
    public function requireRole($role) {
        $this->requireAuth();
        
        if ($this->user['role'] !== $role) {
            ApiResponse::error('Insufficient permissions', 403);
        }
    }
}

// ==================== Sample Usage/Demo ====================

// Initialize database
try {
    $db = new Database('localhost', 'demo_user', 'demo_pass', 'demo_db');
    
    // Create tables if they don't exist (for demo purposes)
    $db->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at DATETIME,
            updated_at DATETIME
        )
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            category VARCHAR(50),
            created_at DATETIME,
            updated_at DATETIME
        )
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            total DECIMAL(10,2) NOT NULL,
            created_at DATETIME,
            updated_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )
    ");
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}

// Initialize other components
$auth = new AuthMiddleware($db);
$userManager = new User($db);
$productManager = new Product($db);
$orderManager = new Order($db);
$fileUploader = new FileUploader(TEMP_DIR . 'uploads/');

// Sample data population (for demo)
try {
    // Check if we already have sample data
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Create sample admin user
        $userManager->create('admin', 'admin@example.com', 'admin123', 'admin');
        
        // Create sample regular user
        $userManager->create('user1', 'user1@example.com', 'user123');
        
        // Create sample products
        $productManager->create('Laptop', 'High performance laptop', 999.99, 10, 'Electronics');
        $productManager->create('Smartphone', 'Latest model smartphone', 699.99, 15, 'Electronics');
        $productManager->create('Headphones', 'Noise cancelling headphones', 199.99, 20, 'Accessories');
    }
} catch (Exception $e) {
    logMessage("Sample data population failed: " . $e->getMessage(), 'ERROR');
}

// ==================== Simple Routing ====================

// For demo purposes, we'll simulate a simple router
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple router simulation
if ($requestUri === '/api/login' && $requestMethod === 'POST') {
    // Login endpoint
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');
    
    if ($auth->authenticate($username, $password)) {
        $user = $auth->getUser();
        ApiResponse::success([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 'Login successful');
    } else {
        ApiResponse::error('Invalid credentials', 401);
    }
} elseif ($requestUri === '/api/products' && $requestMethod === 'GET') {
    // Get products list
    $stmt = $db->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
    ApiResponse::success(['products' => $products]);
} elseif (preg_match('/^\/api\/products\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'GET') {
    // Get single product
    $productId = $matches[1];
    $product = new Product($db);
    
    if ($product->load($productId)) {
        ApiResponse::success(['product' => [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'category' => $product->getCategory()
        ]]);
    } else {
        ApiResponse::notFound('Product not found');
    }
} elseif ($requestUri === '/api/upload' && $requestMethod === 'POST') {
    // File upload endpoint
    $auth->requireAuth();
    
    if (empty($_FILES['file'])) {
        ApiResponse::error('No file uploaded');
    }
    
    try {
        $fileInfo = $fileUploader->upload($_FILES['file']);
        ApiResponse::success(['file' => $fileInfo], 'File uploaded successfully');
    } catch (Exception $e) {
        ApiResponse::error($e->getMessage());
    }
} else {
    // Not found
    ApiResponse::notFound('Endpoint not found');
}

// End of script

<?php
/**
 * Comprehensive PHP Example Script
 * Approximately 1000 lines demonstrating various PHP features
 */

// ==================== Configuration Section ====================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('APP_NAME', 'PHP Demo Application');
define('APP_VERSION', '1.0.0');
define('MAX_LOGIN_ATTEMPTS', 5);
define('TEMP_DIR', __DIR__ . '/temp/');

// ==================== Utility Functions ====================

/**
 * Sanitize user input
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate a random string
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Log message to file
 * @param string $message
 * @param string $type
 */
function logMessage($message, $type = 'INFO') {
    $logFile = TEMP_DIR . 'application.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// ==================== Database Class ====================

class Database {
    private $connection;
    private $host;
    private $username;
    private $password;
    private $database;
    
    public function __construct($host, $username, $password, $database) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database}",
                $this->username,
                $this->password
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logMessage("Database connection failed: " . $e->getMessage(), 'ERROR');
            throw new Exception("Database connection error");
        }
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            logMessage("Query failed: " . $e->getMessage() . " SQL: $sql", 'ERROR');
            throw new Exception("Database query error");
        }
    }
    
    public function getLastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function __destruct() {
        $this->connection = null;
    }
}

// ==================== User Class ====================

class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $role;
    private $createdAt;
    private $updatedAt;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        $stmt = $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
        $userData = $stmt->fetch();
        
        if ($userData) {
            $this->id = $userData['id'];
            $this->username = $userData['username'];
            $this->email = $userData['email'];
            $this->role = $userData['role'];
            $this->createdAt = $userData['created_at'];
            $this->updatedAt = $userData['updated_at'];
            return true;
        }
        
        return false;
    }
    
    public function create($username, $email, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $this->db->query(
            "INSERT INTO users (username, email, password, role, created_at, updated_at) 
             VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$username, $email, $hashedPassword, $role]
        );
        
        $this->id = $this->db->getLastInsertId();
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        
        return $this->id;
    }
    
    public function update($data) {
        $updates = [];
        $params = [];
        
        if (isset($data['username'])) {
            $updates[] = 'username = ?';
            $params[] = $data['username'];
            $this->username = $data['username'];
        }
        
        if (isset($data['email'])) {
            $updates[] = 'email = ?';
            $params[] = $data['email'];
            $this->email = $data['email'];
        }
        
        if (isset($data['password'])) {
            $updates[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        if (isset($data['role'])) {
            $updates[] = 'role = ?';
            $params[] = $data['role'];
            $this->role = $data['role'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = 'updated_at = NOW()';
        $params[] = $this->id;
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $this->db->query($sql, $params);
        
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }
    
    public function delete() {
        $this->db->query("DELETE FROM users WHERE id = ?", [$this->id]);
        $this->id = null;
        return true;
    }
    
    public function authenticate($password) {
        $stmt = $this->db->query("SELECT password FROM users WHERE id = ?", [$this->id]);
        $userData = $stmt->fetch();
        
        if ($userData && password_verify($password, $userData['password'])) {
            return true;
        }
        
        return false;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
}

// ==================== Product Class ====================

class Product {
    private $db;
    private $id;
    private $name;
    private $description;
    private $price;
    private $stock;
    private $category;
    private $createdAt;
    private $updatedAt;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        $stmt = $this->db->query("SELECT * FROM products WHERE id = ?", [$id]);
        $productData = $stmt->fetch();
        
        if ($productData) {
            $this->id = $productData['id'];
            $this->name = $productData['name'];
            $this->description = $productData['description'];
            $this->price = $productData['price'];
            $this->stock = $productData['stock'];
            $this->category = $productData['category'];
            $this->createdAt = $productData['created_at'];
            $this->updatedAt = $productData['updated_at'];
            return true;
        }
        
        return false;
    }
    
    public function create($name, $description, $price, $stock, $category) {
        $this->db->query(
            "INSERT INTO products (name, description, price, stock, category, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
            [$name, $description, $price, $stock, $category]
        );
        
        $this->id = $this->db->getLastInsertId();
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->category = $category;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->updatedAt = date('Y-m-d H:i:s');
        
        return $this->id;
    }
    
    public function update($data) {
        $updates = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updates[] = 'name = ?';
            $params[] = $data['name'];
            $this->name = $data['name'];
        }
        
        if (isset($data['description'])) {
            $updates[] = 'description = ?';
            $params[] = $data['description'];
            $this->description = $data['description'];
        }
        
        if (isset($data['price'])) {
            $updates[] = 'price = ?';
            $params[] = $data['price'];
            $this->price = $data['price'];
        }
        
        if (isset($data['stock'])) {
            $updates[] = 'stock = ?';
            $params[] = $data['stock'];
            $this->stock = $data['stock'];
        }
        
        if (isset($data['category'])) {
            $updates[] = 'category = ?';
            $params[] = $data['category'];
            $this->category = $data['category'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $updates[] = 'updated_at = NOW()';
        $params[] = $this->id;
        
        $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id = ?";
        $this->db->query($sql, $params);
        
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }
    
    public function delete() {
        $this->db->query("DELETE FROM products WHERE id = ?", [$this->id]);
        $this->id = null;
        return true;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getPrice() { return $this->price; }
    public function getStock() { return $this->stock; }
    public function getCategory() { return $this->category; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    
    // Business logic methods
    public function isInStock() {
        return $this->stock > 0;
    }
    
    public function applyDiscount($percent) {
        if ($percent <= 0 || $percent > 100) {
            throw new InvalidArgumentException("Discount percentage must be between 1 and 100");
        }
        
        $this->price = $this->price * (1 - ($percent / 100));
        $this->update(['price' => $this->price]);
    }
}

// ==================== Order Class ====================

class Order {
    private $db;
    private $id;
    private $userId;
    private $status;
    private $total;
    private $items = [];
    private $createdAt;
    private $updatedAt;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function load($id) {
        $stmt = $this->db->query("SELECT * FROM orders WHERE id = ?", [$id]);
        $orderData = $stmt->fetch();
        
        if ($orderData) {
            $this->id = $orderData['id'];
            $this->userId = $orderData['user_id'];
            $this->status = $orderData['status'];
            $this->total = $orderData['total'];
            $this->createdAt = $orderData['created_at'];
            $this->updatedAt = $orderData['updated_at'];
            
            // Load order items
            $stmt = $this->db->query("SELECT * FROM order_items WHERE order_id = ?", [$this->id]);
            $this->items = $stmt->fetchAll();
            
            return true;
        }
        
        return false;
    }
    
    public function create($userId, $items) {
        $this->db->beginTransaction();
        
        try {
            // Create order
            $this->db->query(
                "INSERT INTO orders (user_id, status, total, created_at, updated_at) 
                 VALUES (?, 'pending', 0, NOW(), NOW())",
                [$userId]
            );
            
            $this->id = $this->db->getLastInsertId();
            $this->userId = $userId;
            $this->status = 'pending';
            $this->createdAt = date('Y-m-d H:i:s');
            $this->updatedAt = date('Y-m-d H:i:s');
            
            // Add items and calculate total
            $total = 0;
            foreach ($items as $item) {
                // Get product price
                $productStmt = $this->db->query("SELECT price FROM products WHERE id = ?", [$item['product_id']]);
                $product = $productStmt->fetch();
                
                if (!$product) {
                    throw new Exception("Product not found: " . $item['product_id']);
                }
                
                $itemTotal = $product['price'] * $item['quantity'];
                $total += $itemTotal;
                
                $this->db->query(
                    "INSERT INTO order_items (order_id, product_id, quantity, price, total) 
                     VALUES (?, ?, ?, ?, ?)",
                    [$this->id, $item['product_id'], $item['quantity'], $product['price'], $itemTotal]
                );
                
                $this->items[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                    'total' => $itemTotal
                ];
            }
            
            // Update order total
            $this->db->query("UPDATE orders SET total = ? WHERE id = ?", [$total, $this->id]);
            $this->total = $total;
            
            $this->db->commit();
            return $this->id;
        } catch (Exception $e) {
            $this->db->rollback();
            logMessage("Order creation failed: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    public function updateStatus($status) {
        $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $allowedStatuses)) {
            throw new InvalidArgumentException("Invalid order status");
        }
        
        $this->db->query(
            "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $this->id]
        );
        
        $this->status = $status;
        $this->updatedAt = date('Y-m-d H:i:s');
        return true;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->userId; }
    public function getStatus() { return $this->status; }
    public function getTotal() { return $this->total; }
    public function getItems() { return $this->items; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
}

// ==================== File Uploader Class ====================

class FileUploader {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private $maxSize = 2097152; // 2MB
    
    public function __construct($uploadDir) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    public function upload($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file['error']);
        }
        
        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new Exception("File type not allowed: " . $file['type']);
        }
        
        if ($file['size'] > $this->maxSize) {
            throw new Exception("File size exceeds maximum allowed size");
        }
        
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $file['name']);
        $destination = $this->uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Failed to move uploaded file");
        }
        
        return [
            'original_name' => $file['name'],
            'stored_name' => $filename,
            'path' => $destination,
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }
    
    public function delete($filename) {
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}

// ==================== API Response Class ====================

class ApiResponse {
    private $status;
    private $message;
    private $data;
    private $httpCode;
    
    public function __construct($status = 'success', $message = '', $data = [], $httpCode = 200) {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->httpCode = $httpCode;
    }
    
    public function send() {
        http_response_code($this->httpCode);
        header('Content-Type: application/json');
        
        echo json_encode([
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => time()
        ]);
        
        exit;
    }
    
    public static function success($data = [], $message = 'Operation successful') {
        $response = new self('success', $message, $data, 200);
        $response->send();
    }
    
    public static function error($message = 'An error occurred', $httpCode = 400, $data = []) {
        $response = new self('error', $message, $data, $httpCode);
        $response->send();
    }
    
    public static function notFound($message = 'Resource not found') {
        $response = new self('error', $message, [], 404);
        $response->send();
    }
}

// ==================== Authentication Middleware ====================

class AuthMiddleware {
    private $db;
    private $user;
    
    public function __construct(Database $db) {
        $this->db = $db;
        $this->user = null;
    }
    
    public function authenticate($username, $password) {
        $stmt = $this->db->query("SELECT * FROM users WHERE username = ?", [$username]);
        $userData = $stmt->fetch();
        
        if (!$userData) {
            return false;
        }
        
        if (!password_verify($password, $userData['password'])) {
            return false;
        }
        
        $this->user = $userData;
        return true;
    }
    
    public function getUser() {
        return $this->user;
    }
    
    public function requireAuth() {
        if (!$this->user) {
            ApiResponse::error('Authentication required', 401);
        }
    }
    
    public function requireRole($role) {
        $this->requireAuth();
        
        if ($this->user['role'] !== $role) {
            ApiResponse::error('Insufficient permissions', 403);
        }
    }
}

// ==================== Sample Usage/Demo ====================

// Initialize database
try {
    $db = new Database('localhost', 'demo_user', 'demo_pass', 'demo_db');
    
    // Create tables if they don't exist (for demo purposes)
    $db->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at DATETIME,
            updated_at DATETIME
        )
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            category VARCHAR(50),
            created_at DATETIME,
            updated_at DATETIME
        )
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            total DECIMAL(10,2) NOT NULL,
            created_at DATETIME,
            updated_at DATETIME,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        )
    ");
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}

// Initialize other components
$auth = new AuthMiddleware($db);
$userManager = new User($db);
$productManager = new Product($db);
$orderManager = new Order($db);
$fileUploader = new FileUploader(TEMP_DIR . 'uploads/');

// Sample data population (for demo)
try {
    // Check if we already have sample data
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Create sample admin user
        $userManager->create('admin', 'admin@example.com', 'admin123', 'admin');
        
        // Create sample regular user
        $userManager->create('user1', 'user1@example.com', 'user123');
        
        // Create sample products
        $productManager->create('Laptop', 'High performance laptop', 999.99, 10, 'Electronics');
        $productManager->create('Smartphone', 'Latest model smartphone', 699.99, 15, 'Electronics');
        $productManager->create('Headphones', 'Noise cancelling headphones', 199.99, 20, 'Accessories');
    }
} catch (Exception $e) {
    logMessage("Sample data population failed: " . $e->getMessage(), 'ERROR');
}

// ==================== Simple Routing ====================

// For demo purposes, we'll simulate a simple router
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple router simulation
if ($requestUri === '/api/login' && $requestMethod === 'POST') {
    // Login endpoint
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');
    
    if ($auth->authenticate($username, $password)) {
        $user = $auth->getUser();
        ApiResponse::success([
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ], 'Login successful');
    } else {
        ApiResponse::error('Invalid credentials', 401);
    }
} elseif ($requestUri === '/api/products' && $requestMethod === 'GET') {
    // Get products list
    $stmt = $db->query("SELECT * FROM products");
    $products = $stmt->fetchAll();
    ApiResponse::success(['products' => $products]);
} elseif (preg_match('/^\/api\/products\/(\d+)$/', $requestUri, $matches) && $requestMethod === 'GET') {
    // Get single product
    $productId = $matches[1];
    $product = new Product($db);
    
    if ($product->load($productId)) {
        ApiResponse::success(['product' => [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
            'category' => $product->getCategory()
        ]]);
    } else {
        ApiResponse::notFound('Product not found');
    }
} elseif ($requestUri === '/api/upload' && $requestMethod === 'POST') {
    // File upload endpoint
    $auth->requireAuth();
    
    if (empty($_FILES['file'])) {
        ApiResponse::error('No file uploaded');
    }
    
    try {
        $fileInfo = $fileUploader->upload($_FILES['file']);
        ApiResponse::success(['file' => $fileInfo], 'File uploaded successfully');
    } catch (Exception $e) {
        ApiResponse::error($e->getMessage());
    }
} else {
    // Not found
    ApiResponse::notFound('Endpoint not found');
}

// End of script
