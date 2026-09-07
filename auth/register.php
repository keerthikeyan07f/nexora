<?php
require_once __DIR__ . '/../config/config.php';

if (is_logged_in()) {
    header("Location: ../dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username  = sanitize($_POST['username'] ?? '');
    $email     = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password  = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please complete all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $db = getDBConnection();
        if ($db) {
            // Check if username or email exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Username or email address is already registered.';
            } else {
                $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt_insert = $db->prepare("INSERT INTO users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
                if ($stmt_insert->execute([$username, $email, $pass_hash, $full_name])) {
                    $new_id = $db->lastInsertId();
                    
                    // Create default settings & welcome notification
                    $db->prepare("INSERT INTO user_settings (user_id, theme) VALUES (?, 'dark')")->execute([$new_id]);
                    add_notification($new_id, 'Welcome to NEXORA', 'Your account has been registered successfully. Explore AI stock models!', 'success');

                    $_SESSION['user_id']   = $new_id;
                    $_SESSION['username']  = $username;
                    $_SESSION['email']     = $email;
                    $_SESSION['full_name'] = $full_name;

                    header("Location: ../dashboard.php");
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        } else {
            $error = 'Database is offline. Please run schema.sql in MySQL to start.';
        }
    }
}
$current_theme = get_user_theme();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($current_theme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | NEXORA AI Stock Intelligence</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3" style="background: var(--bg-primary);">

<div class="glass-card shadow-lg p-4 p-md-5" style="max-width: 480px; width: 100%; border-radius: 20px;">
  <div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-dark-subtle mb-3" style="width: 70px; height: 70px; background: rgba(0, 242, 254, 0.1) !important;">
      <i class="fas fa-user-plus text-gradient fs-1"></i>
    </div>
    <h2 class="fw-bold text-heading mb-1">Create Account</h2>
    <p class="text-muted fs-6">Join NEXORA Financial Intelligence System</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger border-0 rounded-3 text-white mb-3" style="background: var(--bear-color);">
      <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="register.php">
    <div class="mb-3">
      <label class="form-label text-muted fw-semibold fs-7">Full Name</label>
      <input type="text" name="full_name" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="Alex Morgan" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label text-muted fw-semibold fs-7">Username *</label>
        <input type="text" name="username" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="alexm" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label text-muted fw-semibold fs-7">Email Address *</label>
        <input type="email" name="email" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="alex@domain.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-4">
        <label class="form-label text-muted fw-semibold fs-7">Password *</label>
        <input type="password" name="password" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="••••••••" required>
      </div>
      <div class="col-md-6 mb-4">
        <label class="form-label text-muted fw-semibold fs-7">Confirm Password *</label>
        <input type="password" name="confirm_password" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="••••••••" required>
      </div>
    </div>

    <button type="submit" class="btn btn-nexora w-100 py-3 fw-bold fs-6 mb-3">
      <i class="fas fa-check-circle me-2"></i> Register Account
    </button>
  </form>

  <div class="text-center mt-3">
    <span class="text-muted fs-7">Already have an account?</span>
    <a href="login.php" class="text-info fw-bold fs-7 ms-1 text-decoration-none">Sign In</a>
  </div>
</div>

</body>
</html>
