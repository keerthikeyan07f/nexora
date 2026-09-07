<?php
define('PAGE_TITLE', 'User Settings');
require_once __DIR__ . '/config/config.php';
require_login();

$user = get_logged_in_user();
$user_id = $user['id'];
$db = getDBConnection();

$msg_success = '';
$msg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email     = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);

        if (empty($email)) {
            $msg_error = 'Please enter a valid email address.';
        } else if ($db) {
            $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $user_id]);
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $msg_success = 'Profile details updated successfully!';
        }
    } elseif (isset($_POST['change_password'])) {
        $cur_pass  = $_POST['current_password'] ?? '';
        $new_pass  = $_POST['new_password'] ?? '';
        $conf_pass = $_POST['confirm_password'] ?? '';

        if (empty($cur_pass) || empty($new_pass)) {
            $msg_error = 'Please fill out all password fields.';
        } elseif ($new_pass !== $conf_pass) {
            $msg_error = 'New passwords do not match.';
        } elseif (strlen($new_pass) < 6) {
            $msg_error = 'Password must be at least 6 characters long.';
        } else if ($db) {
            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $u = $stmt->fetch();
            if ($u && password_verify($cur_pass, $u['password_hash'])) {
                $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $up = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $up->execute([$new_hash, $user_id]);
                $msg_success = 'Password changed successfully!';
            } else {
                $msg_error = 'Current password is incorrect.';
            }
        }
    }
}

// Fetch user settings
$user_settings = ['theme' => 'dark', 'currency' => 'USD', 'email_notifications' => 1, 'prediction_alerts' => 1];
if ($db) {
    $stmt_s = $db->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt_s->execute([$user_id]);
    $res = $stmt_s->fetch();
    if ($res) $user_settings = array_merge($user_settings, $res);
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h2 class="fw-bold text-heading mb-1"><i class="fas fa-cog text-cyan me-2"></i> Settings & Preferences</h2>
        <p class="text-muted mb-0">Manage account details, password security, and theme preferences</p>
      </div>
    </div>

    <?php if (!empty($msg_success)): ?>
      <div class="alert alert-success border-0 rounded-3 text-white mb-4" style="background: var(--bull-color);">
        <i class="fas fa-check-circle me-2"></i> <?php echo $msg_success; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($msg_error)): ?>
      <div class="alert alert-danger border-0 rounded-3 text-white mb-4" style="background: var(--bear-color);">
        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $msg_error; ?>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <!-- Profile Information Settings -->
      <div class="col-lg-6">
        <div class="glass-card h-100">
          <h4 class="fw-bold text-heading mb-3"><i class="fas fa-user-circle text-info me-2"></i> Profile Information</h4>
          
          <form method="POST" action="settings.php">
            <input type="hidden" name="update_profile" value="1">

            <div class="mb-3">
              <label class="form-label text-muted fw-semibold fs-7">Username</label>
              <input type="text" class="form-control border-0 text-muted font-monospace" style="background: var(--bg-input);" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
              <small class="text-muted fs-8">Username cannot be changed.</small>
            </div>

            <div class="mb-3">
              <label class="form-label text-muted fw-semibold fs-7">Full Name</label>
              <input type="text" name="full_name" class="form-control border-0 text-main" style="background: var(--bg-input);" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
            </div>

            <div class="mb-4">
              <label class="form-label text-muted fw-semibold fs-7">Email Address</label>
              <input type="email" name="email" class="form-control border-0 text-main" style="background: var(--bg-input);" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn btn-nexora py-2 px-4 fw-bold">
              <i class="fas fa-save me-1"></i> Update Profile
            </button>
          </form>
        </div>
      </div>

      <!-- Password Change Settings -->
      <div class="col-lg-6">
        <div class="glass-card h-100">
          <h4 class="fw-bold text-heading mb-3"><i class="fas fa-lock text-purple me-2"></i> Password & Security</h4>

          <form method="POST" action="settings.php">
            <input type="hidden" name="change_password" value="1">

            <div class="mb-3">
              <label class="form-label text-muted fw-semibold fs-7">Current Password</label>
              <input type="password" name="current_password" class="form-control border-0 text-main" style="background: var(--bg-input);" required>
            </div>

            <div class="mb-3">
              <label class="form-label text-muted fw-semibold fs-7">New Password</label>
              <input type="password" name="new_password" class="form-control border-0 text-main" style="background: var(--bg-input);" required>
            </div>

            <div class="mb-4">
              <label class="form-label text-muted fw-semibold fs-7">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control border-0 text-main" style="background: var(--bg-input);" required>
            </div>

            <button type="submit" class="btn btn-outline-nexora py-2 px-4 fw-bold">
              <i class="fas fa-key me-1"></i> Update Password
            </button>
          </form>
        </div>
      </div>

      <!-- Display & Appearance -->
      <div class="col-12">
        <div class="glass-card">
          <h4 class="fw-bold text-heading mb-3"><i class="fas fa-palette text-amber me-2"></i> Appearance & Theme System</h4>
          
          <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: var(--bg-input);">
            <div>
              <h6 class="fw-bold text-heading mb-1">Color Mode Theme</h6>
              <p class="text-muted fs-7 mb-0">Switch between NEXORA Cyber Dark Mode and Modern Clean Light Mode.</p>
            </div>
            <button class="btn btn-nexora py-2 px-4 theme-toggle-btn">
              <i class="fas <?php echo ($current_theme === 'dark') ? 'fa-sun' : 'fa-moon'; ?> me-2"></i> Toggle Theme Mode
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
