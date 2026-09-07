<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? 'read_all';
$notif_id = intval($_POST['id'] ?? 0);

$db = getDBConnection();
if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Database offline']);
    exit();
}

if ($action === 'mark_read' && $notif_id > 0) {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);
    echo json_encode(['status' => 'success']);
} elseif ($action === 'read_all') {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification action']);
}
