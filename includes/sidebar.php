<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-header">
    <a href="dashboard.php" class="sidebar-brand text-decoration-none">
      <i class="fas fa-brain-circuit text-gradient"></i>
      <span>NEXORA</span>
    </a>
  </div>
  
  <div class="sidebar-menu">
    <div class="sidebar-category">Overview</div>
    
    <a href="dashboard.php" class="sidebar-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
      <i class="fas fa-chart-pie"></i>
      <span>Dashboard</span>
    </a>

    <a href="explorer.php" class="sidebar-link <?php echo ($current_page == 'explorer.php' || $current_page == 'stock_detail.php') ? 'active' : ''; ?>">
      <i class="fas fa-search-dollar"></i>
      <span>Stock Explorer</span>
    </a>

    <div class="sidebar-category">Intelligence</div>

    <a href="prediction.php" class="sidebar-link <?php echo $current_page == 'prediction.php' ? 'active' : ''; ?>">
      <i class="fas fa-wand-magic-sparkles"></i>
      <span>AI Prediction</span>
    </a>

    <a href="prediction_history.php" class="sidebar-link <?php echo $current_page == 'prediction_history.php' ? 'active' : ''; ?>">
      <i class="fas fa-history"></i>
      <span>Prediction History</span>
    </a>

    <div class="sidebar-category">Portfolio & Assets</div>

    <a href="watchlist.php" class="sidebar-link <?php echo $current_page == 'watchlist.php' ? 'active' : ''; ?>">
      <i class="fas fa-star"></i>
      <span>Watchlist</span>
    </a>

    <a href="portfolio.php" class="sidebar-link <?php echo $current_page == 'portfolio.php' ? 'active' : ''; ?>">
      <i class="fas fa-wallet"></i>
      <span>Virtual Portfolio</span>
    </a>

    <div class="sidebar-category">Account</div>

    <a href="notifications.php" class="sidebar-link <?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>">
      <i class="fas fa-bell"></i>
      <span>Notifications</span>
    </a>

    <a href="settings.php" class="sidebar-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
      <i class="fas fa-sliders-h"></i>
      <span>Settings</span>
    </a>

    <a href="profile.php" class="sidebar-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
      <i class="fas fa-user-circle"></i>
      <span>Profile</span>
    </a>
  </div>

  <div class="sidebar-footer">
    <a href="auth/logout.php" class="sidebar-link text-danger">
      <i class="fas fa-sign-out-alt"></i>
      <span>Sign Out</span>
    </a>
  </div>
</aside>
