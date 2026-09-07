<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$symbol  = strtoupper(trim($_POST['symbol'] ?? ''));
$action  = strtoupper(trim($_POST['action'] ?? 'BUY'));
$quantity = floatval($_POST['quantity'] ?? 0);
$price    = floatval($_POST['price'] ?? 0);

if (empty($symbol) || $quantity <= 0 || $price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid transaction details. Quantity and price must be greater than zero.']);
    exit();
}

$db = getDBConnection();
if (!$db) {
    echo json_encode(['status' => 'error', 'message' => 'Database offline']);
    exit();
}

$total_amount = round($quantity * $price, 2);

try {
    $db->beginTransaction();

    // Fetch existing holding
    $stmt = $db->prepare("SELECT * FROM portfolio WHERE user_id = ? AND symbol = ?");
    $stmt->execute([$user_id, $symbol]);
    $holding = $stmt->fetch();

    if ($action === 'BUY') {
        if ($holding) {
            $new_quantity = $holding['quantity'] + $quantity;
            $new_avg_price = (($holding['quantity'] * $holding['avg_buy_price']) + ($quantity * $price)) / $new_quantity;
            
            $update = $db->prepare("UPDATE portfolio SET quantity = ?, avg_buy_price = ? WHERE id = ?");
            $update->execute([$new_quantity, $new_avg_price, $holding['id']]);
        } else {
            $insert = $db->prepare("INSERT INTO portfolio (user_id, symbol, quantity, avg_buy_price) VALUES (?, ?, ?, ?)");
            $insert->execute([$user_id, $symbol, $quantity, $price]);
        }
        
        $msg = "Executed Virtual BUY order: $quantity shares of $symbol @ $$price";
        add_notification($user_id, 'Portfolio Buy Executed', $msg, 'success');

    } elseif ($action === 'SELL') {
        if (!$holding || $holding['quantity'] < $quantity) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Insufficient holdings to execute sell order.']);
            exit();
        }

        $new_quantity = $holding['quantity'] - $quantity;
        if ($new_quantity <= 0.0001) {
            $del = $db->prepare("DELETE FROM portfolio WHERE id = ?");
            $del->execute([$holding['id']]);
        } else {
            $update = $db->prepare("UPDATE portfolio SET quantity = ? WHERE id = ?");
            $update->execute([$new_quantity, $holding['id']]);
        }

        $msg = "Executed Virtual SELL order: $quantity shares of $symbol @ $$price";
        add_notification($user_id, 'Portfolio Sell Executed', $msg, 'info');
    }

    // Log transaction history
    $log_tx = $db->prepare("INSERT INTO portfolio_transactions (user_id, symbol, transaction_type, quantity, price, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
    $log_tx->execute([$user_id, $symbol, $action, $quantity, $price, $total_amount]);

    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => "Successfully executed $action order for $quantity shares of $symbol."
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
