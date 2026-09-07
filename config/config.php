<?php
/**
 * NEXORA - AI Stock Intelligence
 * Application Configuration & Global Core Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment Constants & Cloud Database Connection Parser
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_NAME') ?: 'nexora_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

// Parse DATABASE_URL / MYSQL_URL if provided by cloud hosting (e.g., Railway / Render / Heroku)
$dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL');
if (!empty($dbUrl)) {
    $parsedUrl = parse_url($dbUrl);
    if ($parsedUrl) {
        $dbHost = $parsedUrl['host'] ?? $dbHost;
        $dbPort = $parsedUrl['port'] ?? $dbPort;
        $dbUser = $parsedUrl['user'] ?? $dbUser;
        $dbPass = $parsedUrl['pass'] ?? $dbPass;
        $dbName = ltrim($parsedUrl['path'] ?? '', '/') ?: $dbName;
    }
}

define('DB_HOST', $dbHost);
define('DB_PORT', $dbPort);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);

define('APP_NAME', 'NEXORA AI');
define('APP_TAGLINE', 'AI STOCK INTELLIGENCE');
define('FLASK_API_URL', getenv('FLASK_API_URL') ?: 'http://127.0.0.1:5000');

/**
 * Database Singleton Connection (PDO)
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error silently and return null or handle gracefully
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
    }
    return $pdo;
}

/**
 * Authentication Middleware & Helpers
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: auth/login.php");
        exit();
    }
}

function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }
    $db = getDBConnection();
    if (!$db) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'demo',
            'email' => $_SESSION['email'] ?? 'demo@nexora.ai',
            'full_name' => $_SESSION['full_name'] ?? 'Nexora User'
        ];
    }
    $stmt = $db->prepare("SELECT id, username, email, full_name, profile_pic, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? 'demo',
        'email' => $_SESSION['email'] ?? 'demo@nexora.ai',
        'full_name' => $_SESSION['full_name'] ?? 'Nexora User'
    ];
}

/**
 * Sanitization & Security Helpers
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * System Notification Helper
 */
function add_notification($user_id, $title, $message, $type = 'info') {
    $db = getDBConnection();
    if ($db && $user_id) {
        try {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $message, $type]);
        } catch (PDOException $e) {
            error_log("Failed to insert notification: " . $e->getMessage());
        }
    }
}

/**
 * User Theme Preference Helper
 */
function get_user_theme($user_id = null) {
    if (!$user_id && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
    }
    if ($user_id) {
        $db = getDBConnection();
        if ($db) {
            $stmt = $db->prepare("SELECT theme FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $res = $stmt->fetch();
            if ($res && isset($res['theme'])) {
                return $res['theme'];
            }
        }
    }
    return $_COOKIE['nexora_theme'] ?? 'dark';
}
