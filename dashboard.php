<?php
define('PAGE_TITLE', 'Market Overview Dashboard');
require_once __DIR__ . '/config/config.php';
require_login();

$user = get_logged_in_user();
$db   = getDBConnection();
$user_id = $user['id'];

// Fetch user watchlist symbols
$watchlist_items = [];
if ($db) {
    $stmt = $db->prepare("SELECT symbol, added_at FROM watchlist WHERE user_id = ? ORDER BY added_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $watchlist_items = $stmt->fetchAll();
}

// Fetch portfolio summary
$portfolio_items = [];
$total_portfolio_value = 0.0;
$total_portfolio_cost = 0.0;

if ($db) {
    $stmt = $db->prepare("SELECT symbol, quantity, avg_buy_price FROM portfolio WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $portfolio_items = $stmt->fetchAll();
    
    // Baseline simulated values for quick preview calculation
    $prices = ['AAPL' => 224.25, 'NVDA' => 128.40, 'MSFT' => 418.50, 'TSLA' => 210.60, 'AMZN' => 186.10];
    foreach ($portfolio_items as $item) {
        $cur_p = $prices[$item['symbol']] ?? $item['avg_buy_price'];
        $val = $item['quantity'] * $cur_p;
        $cost = $item['quantity'] * $item['avg_buy_price'];
        $total_portfolio_value += $val;
        $total_portfolio_cost += $cost;
    }
}
$portfolio_profit = $total_portfolio_value - $total_portfolio_cost;
$portfolio_profit_pct = $total_portfolio_cost > 0 ? round(($portfolio_profit / $total_portfolio_cost) * 100, 2) : 0;

// Fetch recent predictions
$recent_predictions = [];
if ($db) {
    $stmt = $db->prepare("SELECT * FROM predictions WHERE user_id = ? ORDER BY created_at DESC LIMIT 4");
    $stmt->execute([$user_id]);
    $recent_predictions = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <!-- Welcome Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h2 class="fw-bold text-heading mb-1">Market Dashboard</h2>
        <p class="text-muted mb-0">Real-time stock indicators & AI neural forecast summary</p>
      </div>
      <div>
        <a href="prediction.php" class="btn btn-nexora">
          <i class="fas fa-wand-magic-sparkles me-2"></i> Run AI Prediction
        </a>
      </div>
    </div>

    <!-- Metric Stat Cards Row -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="glass-card">
          <div class="stat-card">
            <div>
              <div class="stat-label">Virtual Portfolio Value</div>
              <div class="stat-value">$<?php echo number_format($total_portfolio_value, 2); ?></div>
              <div class="<?php echo $portfolio_profit >= 0 ? 'text-success' : 'text-danger'; ?> fs-7 fw-bold mt-1">
                <i class="fas fa-arrow-<?php echo $portfolio_profit >= 0 ? 'up' : 'down'; ?> me-1"></i>
                $<?php echo number_format(abs($portfolio_profit), 2); ?> (<?php echo $portfolio_profit_pct; ?>%)
              </div>
            </div>
            <div class="stat-icon cyan">
              <i class="fas fa-wallet"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="glass-card">
          <div class="stat-card">
            <div>
              <div class="stat-label">Watchlist Active</div>
              <div class="stat-value"><?php echo count($watchlist_items); ?> Stocks</div>
              <div class="text-muted fs-7 mt-1"><i class="fas fa-eye me-1 text-info"></i> Monitored Daily</div>
            </div>
            <div class="stat-icon purple">
              <i class="fas fa-star"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="glass-card">
          <div class="stat-card">
            <div>
              <div class="stat-label">AI Predictions Logged</div>
              <div class="stat-value"><?php echo count($recent_predictions); ?> Models</div>
              <div class="text-success fs-7 fw-bold mt-1"><i class="fas fa-check-circle me-1"></i> LSTM Active</div>
            </div>
            <div class="stat-icon emerald">
              <i class="fas fa-brain"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="glass-card">
          <div class="stat-card">
            <div>
              <div class="stat-label">Model Evaluation MAE</div>
              <div class="stat-value">1.82 USD</div>
              <div class="text-info fs-7 fw-bold mt-1"><i class="fas fa-chart-line me-1"></i> High Accuracy</div>
            </div>
            <div class="stat-icon amber">
              <i class="fas fa-bullseye"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Chart & Top Movers Section -->
    <div class="row g-4 mb-4">
      <div class="col-lg-8">
        <div class="glass-card h-100">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h4 class="fw-bold text-heading mb-0"><i class="fas fa-chart-line text-info me-2"></i> AAPL - Apple Inc. Historical Trend</h4>
              <small class="text-muted">Interactive 6-Month Daily Candle Close Vector</small>
            </div>
            <div class="d-flex gap-1" id="dashTimeFilter">
              <button class="time-btn" onclick="loadDashChart('1m', this)">1M</button>
              <button class="time-btn active" onclick="loadDashChart('6m', this)">6M</button>
              <button class="time-btn" onclick="loadDashChart('1y', this)">1Y</button>
            </div>
          </div>
          <div style="height: 320px; position: relative;">
            <canvas id="dashMainChart"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="glass-card h-100">
          <h4 class="fw-bold text-heading mb-3"><i class="fas fa-bolt text-warning me-2"></i> Top Market Movers</h4>
          
          <ul class="nav nav-pills mb-3 border-bottom pb-2" id="moversTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active py-1 px-3 fs-7 fw-bold" id="gainers-tab" data-bs-toggle="tab" data-bs-target="#gainers-pane" type="button">Top Gainers</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link py-1 px-3 fs-7 fw-bold" id="losers-tab" data-bs-toggle="tab" data-bs-target="#losers-pane" type="button">Top Losers</button>
            </li>
          </ul>

          <div class="tab-content" id="moversTabContent">
            <div class="tab-pane fade show active" id="gainers-pane">
              <div class="d-flex flex-column gap-2" id="dashGainersList">
                <div class="text-center py-3 text-muted"><div class="loader-spinner mx-auto"></div></div>
              </div>
            </div>
            <div class="tab-pane fade" id="losers-pane">
              <div class="d-flex flex-column gap-2" id="dashLosersList">
                <div class="text-center py-3 text-muted"><div class="loader-spinner mx-auto"></div></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Watchlist & Recent AI Predictions Row -->
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="glass-card">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-heading mb-0"><i class="fas fa-star text-warning me-2"></i> Watchlist Overview</h4>
            <a href="watchlist.php" class="text-info fs-7 fw-bold text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
          </div>

          <?php if (empty($watchlist_items)): ?>
            <div class="empty-state py-4">
              <i class="far fa-star"></i>
              <p class="mb-0">Your watchlist is currently empty.</p>
              <a href="explorer.php" class="btn btn-outline-nexora btn-sm mt-2">Explore Stocks</a>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table custom-table mb-0">
                <thead>
                  <tr>
                    <th>Symbol</th>
                    <th>Current Price</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($watchlist_items as $w): ?>
                    <tr>
                      <td class="fw-bold text-heading">
                        <a href="stock_detail.php?symbol=<?php echo urlencode($w['symbol']); ?>" class="text-decoration-none text-heading">
                          <?php echo htmlspecialchars($w['symbol']); ?>
                        </a>
                      </td>
                      <td>$<?php echo number_format($prices[$w['symbol']] ?? 180.00, 2); ?></td>
                      <td>
                        <a href="stock_detail.php?symbol=<?php echo urlencode($w['symbol']); ?>" class="btn btn-outline-nexora btn-sm py-1 px-2 fs-8">
                          <i class="fas fa-chart-area me-1"></i> Analyze
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="glass-card">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-heading mb-0"><i class="fas fa-wand-magic-sparkles text-gradient me-2"></i> Recent AI Forecasts</h4>
            <a href="prediction_history.php" class="text-info fs-7 fw-bold text-decoration-none">Full History <i class="fas fa-arrow-right ms-1"></i></a>
          </div>

          <?php if (empty($recent_predictions)): ?>
            <div class="p-3 text-center text-muted">
              <p class="mb-2 fs-7">No predictions logged yet. Run your first LSTM model calculation!</p>
              <a href="prediction.php" class="btn btn-nexora btn-sm py-1 px-3">Run Model</a>
            </div>
          <?php else: ?>
            <div class="d-flex flex-column gap-2">
              <?php foreach ($recent_predictions as $p): ?>
                <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: var(--bg-input);">
                  <div>
                    <span class="fw-bold text-heading me-2"><?php echo htmlspecialchars($p['symbol']); ?></span>
                    <span class="text-muted fs-7">Current: $<?php echo number_format($p['current_price'], 2); ?></span>
                  </div>
                  <div class="text-end">
                    <div class="fw-bold text-info">$<?php echo number_format($p['predicted_price'], 2); ?></div>
                    <small class="<?php echo $p['predicted_change_pct'] >= 0 ? 'text-success' : 'text-danger'; ?> fs-8 fw-bold">
                      <?php echo ($p['predicted_change_pct'] >= 0 ? '+' : '') . $p['predicted_change_pct']; ?>%
                    </small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
let mainChartInstance = null;

document.addEventListener('DOMContentLoaded', () => {
  loadDashChart('6m');
  loadTopMovers();
});

function loadDashChart(period, btn) {
  if (btn) {
    document.querySelectorAll('#dashTimeFilter .time-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }

  fetch(`api/stock_proxy.php?action=quote&symbol=AAPL&period=${period}`)
    .then(res => res.json())
    .then(data => {
      const dates = data.history.map(h => h.date);
      const prices = data.history.map(h => h.close);

      const ctx = document.getElementById('dashMainChart').getContext('2d');
      if (mainChartInstance) mainChartInstance.destroy();

      mainChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: dates,
          datasets: [{
            label: 'AAPL Close Price ($)',
            data: prices,
            borderColor: '#00f2fe',
            borderWidth: 2,
            pointRadius: 0,
            pointHoverRadius: 5,
            fill: true,
            backgroundColor: (context) => {
              const chart = context.chart;
              const {ctx, chartArea} = chart;
              if (!chartArea) return null;
              const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
              gradient.addColorStop(0, 'rgba(0, 242, 254, 0.35)');
              gradient.addColorStop(1, 'rgba(0, 242, 254, 0.0)');
              return gradient;
            },
            tension: 0.2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { grid: { display: false }, ticks: { color: '#8a99ad', maxTicksLimit: 8 } },
            y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#8a99ad' } }
          }
        }
      });
    });
}

function loadTopMovers() {
  fetch('api/stock_proxy.php?action=movers')
    .then(res => res.json())
    .then(data => {
      const gainersBox = document.getElementById('dashGainersList');
      const losersBox  = document.getElementById('dashLosersList');

      gainersBox.innerHTML = data.gainers.map(g => `
        <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: var(--bg-input);">
          <div>
            <div class="fw-bold text-heading">${g.symbol}</div>
            <small class="text-muted" style="font-size:0.75rem;">${g.name}</small>
          </div>
          <div class="text-end">
            <div class="fw-bold text-heading">$${g.price.toFixed(2)}</div>
            <span class="badge-bullish fs-8">+${g.change_pct}%</span>
          </div>
        </div>
      `).join('');

      losersBox.innerHTML = data.losers.map(l => `
        <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: var(--bg-input);">
          <div>
            <div class="fw-bold text-heading">${l.symbol}</div>
            <small class="text-muted" style="font-size:0.75rem;">${l.name}</small>
          </div>
          <div class="text-end">
            <div class="fw-bold text-heading">$${l.price.toFixed(2)}</div>
            <span class="badge-bearish fs-8">${l.change_pct}%</span>
          </div>
        </div>
      `).join('');
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
