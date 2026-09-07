<?php
require_once __DIR__ . '/../config/config.php';

if (is_logged_in()) {
    header("Location: ../dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email/username and password.';
    } else {
        $db = getDBConnection();
        if ($db) {
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                
                // Load theme setting
                $stmt_theme = $db->prepare("SELECT theme FROM user_settings WHERE user_id = ?");
                $stmt_theme->execute([$user['id']]);
                $setting = $stmt_theme->fetch();
                if ($setting && !empty($setting['theme'])) {
                    setcookie('nexora_theme', $setting['theme'], time() + (86400 * 365), "/");
                }
                
                header("Location: ../dashboard.php");
                exit();
            } else {
                $error = 'Invalid email/username or password.';
            }
        } else {
            // Fallback mode for demonstration if database is not initialized yet
            if (($email === 'demo@nexora.ai' || $email === 'demouser') && $password === 'DemoUser123!') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'demouser';
                $_SESSION['email'] = 'demo@nexora.ai';
                $_SESSION['full_name'] = 'Nexora Demo User';
                header("Location: ../dashboard.php");
                exit();
            } else {
                $error = 'Database connection failed. Make sure MySQL is running or use demo credentials (demo@nexora.ai / DemoUser123!).';
            }
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
  <title>Login | NEXORA AI Stock Intelligence</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3" style="background: var(--bg-primary);">

<div class="glass-card shadow-lg p-4 p-md-5" style="max-width: 440px; width: 100%; border-radius: 20px;">
  <div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-dark-subtle mb-3" style="width: 70px; height: 70px; background: rgba(0, 242, 254, 0.1) !important;">
      <i class="fas fa-brain-circuit text-gradient fs-1"></i>
    </div>
    <h2 class="fw-bold text-heading mb-1">Welcome Back</h2>
    <p class="text-muted fs-6">Access AI Market Intelligence & Predictive Analytics</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 border-0 rounded-3 text-white" style="background: var(--bear-color);">
      <i class="fas fa-exclamation-triangle"></i>
      <div><?php echo $error; ?></div>
    </div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="mb-3">
      <label class="form-label text-muted fw-semibold fs-7">Email or Username</label>
      <div class="input-group">
        <span class="input-group-text border-0 text-muted" style="background: var(--bg-input);"><i class="fas fa-envelope"></i></span>
        <input type="text" name="email" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="demo@nexora.ai" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
      </div>
    </div>

    <div class="mb-4">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="form-label text-muted fw-semibold fs-7 mb-0">Password</label>
      </div>
      <div class="input-group">
        <span class="input-group-text border-0 text-muted" style="background: var(--bg-input);"><i class="fas fa-lock"></i></span>
        <input type="password" name="password" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="••••••••" required>
      </div>
    </div>

    <button type="submit" class="btn btn-nexora w-100 py-3 fw-bold fs-6 mb-3">
      <i class="fas fa-sign-in-alt me-2"></i> Sign In to Platform
    </button>
  </form>

  <div class="p-3 rounded-3 mb-4" style="background: rgba(0, 242, 254, 0.05); border: 1px dashed rgba(0, 242, 254, 0.2);">
    <small class="d-block text-muted text-center fw-bold mb-1"><i class="fas fa-key me-1 text-info"></i> Demo Credentials</small>
    <div class="d-flex justify-content-between text-muted fs-7">
      <span>Email: <code>demo@nexora.ai</code></span>
      <span>Password: <code>DemoUser123!</code></span>
    </div>
  </div>

  <div class="text-center">
    <span class="text-muted fs-7">Don't have an account?</span>
    <a href="register.php" class="text-info fw-bold fs-7 ms-1 text-decoration-none">Create Account</a>
  </div>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
