<?php
define('PAGE_TITLE', 'Notifications');
require_once __DIR__ . '/config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$db = getDBConnection();

$notifications = [];
if ($db) {
    $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();

    // Mark all as read when opening notification page
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-heading mb-1"><i class="fas fa-bell text-info me-2"></i> System Notifications</h2>
        <p class="text-muted mb-0">Platform updates, portfolio execution alerts, and AI prediction events</p>
      </div>
    </div>

    <?php if (empty($notifications)): ?>
      <div class="glass-card text-center py-5">
        <div class="empty-state">
          <i class="far fa-bell-slash"></i>
          <h4 class="fw-bold text-heading mb-2">No Notifications</h4>
          <p class="text-muted mb-0">You have no system notifications at this time.</p>
        </div>
      </div>
    <?php else: ?>

      <div class="d-flex flex-column gap-3">
        <?php foreach ($notifications as $n): ?>
          <div class="glass-card p-3 d-flex align-items-start gap-3">
            <div class="stat-icon <?php 
              echo $n['type'] === 'success' ? 'emerald' : ($n['type'] === 'warning' ? 'amber' : ($n['type'] === 'danger' ? 'rose' : 'cyan')); 
            ?>" style="width: 44px; height: 44px; font-size: 1.1rem; flex-shrink: 0;">
              <i class="fas <?php 
                echo $n['type'] === 'success' ? 'fa-check-circle' : ($n['type'] === 'warning' ? 'fa-exclamation-triangle' : ($n['type'] === 'danger' ? 'fa-times-circle' : 'fa-info-circle')); 
              ?>"></i>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="fw-bold text-heading mb-0"><?php echo htmlspecialchars($n['title']); ?></h6>
                <small class="text-muted fs-8"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></small>
              </div>
              <p class="text-muted fs-7 mb-0"><?php echo htmlspecialchars($n['message']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
