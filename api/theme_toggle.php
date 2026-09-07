<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

$theme = strtolower($_POST['theme'] ?? 'dark');
if (!in_array($theme, ['dark', 'light'])) {
    $theme = 'dark';
}

setcookie('nexora_theme', $theme, time() + (86400 * 365), "/");

if (is_logged_in()) {
    $db = getDBConnection();
    if ($db) {
        $user_id = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO user_settings (user_id, theme) VALUES (?, ?) ON DUPLICATE KEY UPDATE theme = ?");
        $stmt->execute([$user_id, $theme, $theme]);
    }
}

echo json_encode(['status' => 'success', 'theme' => $theme]);
