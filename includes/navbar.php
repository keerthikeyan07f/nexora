<?php
$user = get_logged_in_user();
$db = getDBConnection();
$unread_count = 0;
if ($db && $user) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user['id']]);
    $unread_count = $stmt->fetchColumn();
}
?>
<header class="top-navbar">
  <div class="d-flex align-items-center gap-3">
    <button class="btn btn-icon-nav d-lg-none" id="mobileSidebarToggle" aria-label="Toggle Sidebar">
      <i class="fas fa-bars"></i>
    </button>

    <form action="explorer.php" method="GET" class="nav-search d-none d-md-block">
      <i class="fas fa-search"></i>
      <input type="text" name="search" placeholder="Search ticker or company (e.g. AAPL, NVDA)..." autocomplete="off" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
    </form>
  </div>

  <div class="nav-actions">
    <button class="btn-icon-nav theme-toggle-btn" title="Toggle Theme" aria-label="Toggle Dark/Light Theme">
      <i class="fas <?php echo ($current_theme === 'dark') ? 'fa-sun' : 'fa-moon'; ?>"></i>
    </button>

    <a href="notifications.php" class="btn-icon-nav" title="Notifications" aria-label="View Notifications">
      <i class="fas fa-bell"></i>
      <?php if ($unread_count > 0): ?>
        <span class="badge-dot"></span>
      <?php endif; ?>
    </a>

    <div class="dropdown">
      <button class="btn p-0 border-0 d-flex align-items-center gap-2 text-decoration-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="stat-icon cyan" style="width: 40px; height: 40px; font-size: 1rem;">
          <i class="fas fa-user"></i>
        </div>
        <div class="d-none d-sm-block text-start">
          <div class="fw-bold text-heading fs-6" style="line-height: 1.1;"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
          <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($user['email']); ?></small>
        </div>
        <i class="fas fa-chevron-down text-muted ms-1" style="font-size: 0.75rem;"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end glass-card p-2 border-0 shadow-lg mt-2" style="width: 220px; background: var(--bg-secondary);">
        <li><a class="dropdown-item rounded-2 py-2 text-main" href="profile.php"><i class="fas fa-user-circle me-2 text-info"></i> Profile</a></li>
        <li><a class="dropdown-item rounded-2 py-2 text-main" href="settings.php"><i class="fas fa-cog me-2 text-primary"></i> Settings</a></li>
        <li><hr class="dropdown-divider border-secondary opacity-25"></li>
        <li><a class="dropdown-item rounded-2 py-2 text-danger" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a></li>
      </ul>
    </div>
  </div>
</header>
