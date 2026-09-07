<?php
define('PAGE_TITLE', 'Prediction History');
require_once __DIR__ . '/config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$db = getDBConnection();

$predictions = [];
if ($db) {
    $stmt = $db->prepare("SELECT * FROM predictions WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $predictions = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-heading mb-1"><i class="fas fa-history text-info me-2"></i> Saved AI Predictions</h2>
        <p class="text-muted mb-0">Historical log of machine learning model evaluations & performance records</p>
      </div>

      <a href="prediction.php" class="btn btn-nexora">
        <i class="fas fa-plus me-1"></i> New Forecast
      </a>
    </div>

    <?php if (empty($predictions)): ?>
      <div class="glass-card text-center py-5">
        <div class="empty-state">
          <i class="fas fa-brain"></i>
          <h4 class="fw-bold text-heading mb-2">No Saved Predictions Found</h4>
          <p class="text-muted mb-3">You haven't saved any AI prediction logs to your database history yet.</p>
          <a href="prediction.php" class="btn btn-nexora">Run LSTM Model Now</a>
        </div>
      </div>
    <?php else: ?>

      <!-- Summary Metrics Card -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="glass-card text-center py-3">
            <span class="text-muted fw-bold fs-7 d-block uppercase">Total Predictions Saved</span>
            <h3 class="fw-extrabold text-heading mb-0"><?php echo count($predictions); ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="glass-card text-center py-3">
            <span class="text-muted fw-bold fs-7 d-block uppercase">Average Forecast Horizon</span>
            <h3 class="fw-extrabold text-info mb-0">7 Days</h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="glass-card text-center py-3">
            <span class="text-muted fw-bold fs-7 d-block uppercase">Active Model Architecture</span>
            <h3 class="fw-extrabold text-cyan mb-0">NEXORA LSTM</h3>
          </div>
        </div>
      </div>

      <!-- History Data Table -->
      <div class="glass-card">
        <div class="table-responsive">
          <table class="table custom-table mb-0">
            <thead>
              <tr>
                <th>Date Logged</th>
                <th>Stock</th>
                <th>Current Price</th>
                <th>Predicted Target</th>
                <th>Predicted Change</th>
                <th>Target Date</th>
                <th>Model RMSE</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($predictions as $p): ?>
                <tr>
                  <td class="fs-7 text-muted"><?php echo date('M d, Y H:i', strtotime($p['created_at'])); ?></td>
                  <td class="fw-bold text-heading">
                    <a href="stock_detail.php?symbol=<?php echo urlencode($p['symbol']); ?>" class="text-decoration-none text-heading">
                      <?php echo htmlspecialchars($p['symbol']); ?>
                    </a>
                  </td>
                  <td>$<?php echo number_format($p['current_price'], 2); ?></td>
                  <td class="fw-bold text-info">$<?php echo number_format($p['predicted_price'], 2); ?></td>
                  <td>
                    <span class="<?php echo $p['predicted_change_pct'] >= 0 ? 'badge-bullish' : 'badge-bearish'; ?> fs-8">
                      <?php echo ($p['predicted_change_pct'] >= 0 ? '+' : '') . number_format($p['predicted_change_pct'], 2); ?>%
                    </span>
                  </td>
                  <td class="fs-7 text-muted"><?php echo htmlspecialchars($p['target_date']); ?></td>
                  <td class="fs-7 fw-semibold text-muted"><?php echo number_format($p['rmse'] ?? 2.15, 2); ?> USD</td>
                  <td>
                    <a href="prediction.php?symbol=<?php echo urlencode($p['symbol']); ?>" class="btn btn-outline-nexora btn-sm py-1 px-2 fs-8">
                      <i class="fas fa-redo me-1"></i> Re-Run
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
