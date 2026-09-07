<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$symbol  = strtoupper(trim($_POST['symbol'] ?? ''));

if (empty($symbol)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid stock symbol']);
    exit();
}

$db = getDBConnection();
if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Database offline']);
    exit();
}

// Check if symbol exists in watchlist
$stmt = $db->prepare("SELECT id FROM watchlist WHERE user_id = ? AND symbol = ?");
$stmt->execute([$user_id, $symbol]);
$item = $stmt->fetch();

if ($item) {
    // Remove from watchlist
    $stmt_del = $db->prepare("DELETE FROM watchlist WHERE id = ?");
    $stmt_del->execute([$item['id']]);
    add_notification($user_id, 'Watchlist Updated', "Removed $symbol from your watchlist.", 'info');
    echo json_encode(['status' => 'removed', 'symbol' => $symbol]);
} else {
    // Add to watchlist
    $stmt_add = $db->prepare("INSERT INTO watchlist (user_id, symbol) VALUES (?, ?)");
    $stmt_add->execute([$user_id, $symbol]);
    add_notification($user_id, 'Watchlist Updated', "Added $symbol to your watchlist.", 'success');
    echo json_encode(['status' => 'added', 'symbol' => $symbol]);
}
