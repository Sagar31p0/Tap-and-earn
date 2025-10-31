<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u988479389_tery');
define('DB_USER', 'u988479389_tery');
define('DB_PASS', 'your_password_here'); // Update with actual password

// Check if database credentials are set
if (DB_PASS === 'your_password_here') {
    error_log("WARNING: Database password not configured in config.php");
}

// Bot Configuration
define('BOT_TOKEN', 'YOUR_BOT_TOKEN_HERE'); // Update with actual bot token
define('BOT_USERNAME', '@CoinTapProBot');
define('BASE_URL', 'https://go.teraboxurll.in');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_start();

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// CORS Headers for API
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
    exit(0);
}

// Database Connection Class
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            // For web pages (not API), show user-friendly error
            if (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
                http_response_code(500);
                echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Database Error</title></head><body style="font-family:Arial;padding:50px;text-align:center;"><h1>Service Unavailable</h1><p>Unable to connect to the database. Please try again later.</p></body></html>';
                exit;
            }
            die(json_encode(['success' => false, 'error' => 'Database connection failed']));
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserializing
    private function __wakeup() {}
}

// Helper Functions
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function validateTelegramWebAppData($initData) {
    // Telegram WebApp data validation
    // This should validate the data sent from Telegram Mini App
    return true; // Implement proper validation in production
}

function getSetting($key, $default = null) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function updateSetting($key, $value) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

function addTransaction($userId, $type, $amount, $description = '') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$userId, $type, $amount, $description]);
}

function updateUserCoins($userId, $amount, $add = true) {
    $db = Database::getInstance()->getConnection();
    $operator = $add ? '+' : '-';
    $stmt = $db->prepare("UPDATE users SET coins = coins $operator ? WHERE id = ?");
    return $stmt->execute([$amount, $userId]);
}

function logAdEvent($userId, $placement, $adUnitId, $event) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("INSERT INTO ad_logs (user_id, placement, ad_unit_id, event) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$userId, $placement, $adUnitId, $event]);
}

function isAdmin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit;
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateReferralCode($length = 8) {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, $length));
}

function updateUserEnergy($userId) {
    $db = Database::getInstance()->getConnection();
    
    // Get current user energy and last update time
    $stmt = $db->prepare("SELECT energy, last_energy_update FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if ($user) {
        $currentTime = time();
        $lastUpdate = strtotime($user['last_energy_update']);
        $timeDiff = $currentTime - $lastUpdate;
        
        // Get recharge settings
        $rechargeInterval = (int)getSetting('energy_recharge_interval', 300); // 5 minutes default
        $rechargeRate = (int)getSetting('energy_recharge_rate', 5);
        
        // Calculate energy to add
        $energyToAdd = floor($timeDiff / $rechargeInterval) * $rechargeRate;
        
        if ($energyToAdd > 0) {
            $newEnergy = min(100, $user['energy'] + $energyToAdd);
            $stmt = $db->prepare("UPDATE users SET energy = ?, last_energy_update = NOW() WHERE id = ?");
            $stmt->execute([$newEnergy, $userId]);
            return $newEnergy;
        }
        
        return $user['energy'];
    }
    
    return 100;
}
?>
