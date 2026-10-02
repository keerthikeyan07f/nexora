<?php
define('PAGE_TITLE', 'AI Stock Intelligence');
require_once __DIR__ . '/config/config.php';
$current_theme = get_user_theme();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($current_theme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NEXORA - Next-Gen AI Stock Market Intelligence & LSTM Predictions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .landing-hero {
      position: relative;
      padding: 100px 0 80px 0;
      background: radial-gradient(circle at 50% 20%, rgba(0, 242, 254, 0.12) 0%, rgba(112, 0, 255, 0.05) 50%, transparent 80%);
    }
    .feature-card-icon {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      margin-bottom: 1.25rem;
    }
  </style>
</head>
<body>

<!-- Landing Navbar -->
<nav class="navbar navbar-expand-lg py-3 border-bottom" style="background: var(--bg-secondary); border-color: var(--border-color) !important;">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-3 text-heading" href="index.php">
      <i class="fas fa-brain-circuit text-gradient"></i>
      <span>NEXORA</span>
    </a>
    <div class="d-flex align-items-center gap-3">
      <button class="btn-icon-nav theme-toggle-btn me-2" title="Toggle Theme">
        <i class="fas <?php echo ($current_theme === 'dark') ? 'fa-sun' : 'fa-moon'; ?>"></i>
      </button>
      <?php if (is_logged_in()): ?>
        <a href="dashboard.php" class="btn btn-nexora">
          <i class="fas fa-chart-line me-2"></i> Go to Dashboard
        </a>
      <?php else: ?>
        <a href="auth/login.php" class="btn btn-outline-nexora">Sign In</a>
        <a href="auth/register.php" class="btn btn-nexora">Get Started </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<section class="landing-hero">
  <div class="container text-center">
    <div class="badge-bullish mb-4 px-3 py-2 fs-6">
      <i class="fas fa-microchip me-2"></i> Powered by TensorFlow LSTM Deep Learning Engine
    </div>
    <h1 class="display-3 fw-extrabold text-heading mb-3">
      Predict Stock Trends with <br><span class="text-gradient">Deep Neural Intelligence</span>
    </h1>
    <p class="lead text-muted mx-auto mb-5" style="max-width: 720px; font-size: 1.2rem;">
      NEXORA leverages sequential Long Short-Term Memory (LSTM) networks to analyze historical price vectors, moving averages, and technical indicators, empowering high-accuracy stock market forecasting.
    </p>

    <div class="d-flex justify-content-center align-items-center gap-3 mb-5 flex-wrap">
      <a href="<?php echo is_logged_in() ? 'dashboard.php' : 'auth/register.php'; ?>" class="btn btn-nexora btn-lg px-4 py-3 fw-bold">
        <i class="fas fa-rocket me-2"></i> Launch AI Dashboard
      </a>
      <a href="explorer.php" class="btn btn-outline-nexora btn-lg px-4 py-3 fw-bold">
        <i class="fas fa-search me-2"></i> Explore Live Markets
      </a>
    </div>

    <!-- Live Market Ticker Strip -->
    <div class="row g-3 justify-content-center">
      <div class="col-6 col-md-3 col-lg-2">
        <div class="glass-card text-center p-3">
          <span class="text-muted fw-bold d-block fs-7">AAPL</span>
          <span class="fw-bold text-heading fs-5">$224.25</span>
          <span class="badge-bullish d-inline-block mt-1 fs-8">+1.85%</span>
        </div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="glass-card text-center p-3">
          <span class="text-muted fw-bold d-block fs-7">NVDA</span>
          <span class="fw-bold text-heading fs-5">$128.40</span>
          <span class="badge-bullish d-inline-block mt-1 fs-8">+4.12%</span>
        </div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="glass-card text-center p-3">
          <span class="text-muted fw-bold d-block fs-7">MSFT</span>
          <span class="fw-bold text-heading fs-5">$418.50</span>
          <span class="badge-bullish d-inline-block mt-1 fs-8">+0.75%</span>
        </div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="glass-card text-center p-3">
          <span class="text-muted fw-bold d-block fs-7">TSLA</span>
          <span class="fw-bold text-heading fs-5">$210.60</span>
          <span class="badge-bearish d-inline-block mt-1 fs-8">-1.42%</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Grid -->
<section class="py-5" style="background: var(--bg-secondary);">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-heading display-5 mb-2">Institutional-Grade Intelligence</h2>
      <p class="text-muted fs-5">Designed for financial analysts, traders, and computer vision researchers</p>
    </div>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="glass-card h-100 p-4">
          <div class="feature-card-icon cyan">
            <i class="fas fa-brain"></i>
          </div>
          <h4 class="fw-bold text-heading mb-2">LSTM Neural Network</h4>
          <p class="text-muted">Multi-layered sequential LSTM neural network architecture trained on 60-day historical time windows to capture price momentum and volatility trends.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="glass-card h-100 p-4">
          <div class="feature-card-icon purple">
            <i class="fas fa-chart-area"></i>
          </div>
          <h4 class="fw-bold text-heading mb-2">Technical Indicators</h4>
          <p class="text-muted">Real-time overlay calculations for 20-day Simple Moving Average (SMA), 50-day EMA, 14-period RSI, and MACD divergence lines.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="glass-card h-100 p-4">
          <div class="feature-card-icon emerald">
            <i class="fas fa-wallet"></i>
          </div>
          <h4 class="fw-bold text-heading mb-2">Virtual Portfolio</h4>
          <p class="text-muted">Simulated trading sandbox enabling risk-free execution of virtual Buy/Sell positions with real-time portfolio valuation and P&L tracking.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="py-4 border-top text-center text-muted" style="background: var(--bg-primary); border-color: var(--border-color) !important;">
  <div class="container">
    <p class="mb-0 fs-7">&copy; 2026 NEXORA AI Stock Intelligence. Final Year College Project Demonstration. Disclaimer: AI predictions are statistical models and do not constitute financial advice.</p>
  </div>
</footer>

<script src="assets/js/app.js"></script>
</body>
</html>
