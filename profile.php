<?php
define('PAGE_TITLE', 'User Profile');
require_once __DIR__ . '/config/config.php';
require_login();

$user = get_logged_in_user();
$user_id = $user['id'];
$db = getDBConnection();

$stats = ['watchlist' => 0, 'portfolio' => 0, 'predictions' => 0];
if ($db) {
    $stmt_w = $db->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ?");
    $stmt_w->execute([$user_id]);
    $stats['watchlist'] = $stmt_w->fetchColumn();

    $stmt_p = $db->prepare("SELECT COUNT(*) FROM portfolio WHERE user_id = ?");
    $stmt_p->execute([$user_id]);
    $stats['portfolio'] = $stmt_p->fetchColumn();

    $stmt_pr = $db->prepare("SELECT COUNT(*) FROM predictions WHERE user_id = ?");
    $stmt_pr->execute([$user_id]);
    $stats['predictions'] = $stmt_pr->fetchColumn();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <div class="glass-card mb-4 p-4">
      <div class="d-flex align-items-center gap-4 flex-wrap">
        <div class="stat-icon cyan" style="width: 90px; height: 90px; font-size: 2.5rem; border-radius: 20px;">
          <i class="fas fa-user-tie"></i>
        </div>
        <div>
          <h2 class="fw-bold text-heading mb-1"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h2>
          <p class="text-muted mb-2"><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
          <div class="d-flex gap-2">
            <span class="badge bg-primary px-3 py-1 fs-8">Standard Analyst Tier</span>
            <span class="badge bg-secondary px-3 py-1 fs-8">Member since <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- User Account Activity Statistics -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="glass-card text-center py-4">
          <div class="stat-icon cyan mx-auto mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-star"></i>
          </div>
          <span class="text-muted fw-bold fs-7 d-block uppercase mb-1">Watchlist Items</span>
          <h3 class="fw-extrabold text-heading mb-0"><?php echo $stats['watchlist']; ?></h3>
        </div>
      </div>

      <div class="col-md-4">
        <div class="glass-card text-center py-4">
          <div class="stat-icon emerald mx-auto mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-wallet"></i>
          </div>
          <span class="text-muted fw-bold fs-7 d-block uppercase mb-1">Active Portfolio Positions</span>
          <h3 class="fw-extrabold text-heading mb-0"><?php echo $stats['portfolio']; ?></h3>
        </div>
      </div>

      <div class="col-md-4">
        <div class="glass-card text-center py-4">
          <div class="stat-icon purple mx-auto mb-2" style="width: 50px; height: 50px;">
            <i class="fas fa-brain"></i>
          </div>
          <span class="text-muted fw-bold fs-7 d-block uppercase mb-1">Saved AI Models</span>
          <h3 class="fw-extrabold text-heading mb-0"><?php echo $stats['predictions']; ?></h3>
        </div>
      </div>
    </div>

    <div class="glass-card text-end">
      <a href="settings.php" class="btn btn-nexora">
        <i class="fas fa-cog me-2"></i> Edit Account Settings
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
