<?php
define('PAGE_TITLE', 'Stock Explorer');
require_once __DIR__ . '/config/config.php';
require_login();

$search_query = trim($_GET['search'] ?? '');
$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// Default stock symbols list to display
$symbols = ['AAPL', 'MSFT', 'GOOGL', 'AMZN', 'NVDA', 'TSLA', 'META', 'NFLX'];

if ($db) {
    if (!empty($search_query)) {
        $stmt = $db->prepare("SELECT symbol FROM stocks WHERE symbol LIKE ? OR company_name LIKE ?");
        $stmt->execute(["%$search_query%", "%$search_query%"]);
        $found = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!empty($found)) {
            $symbols = $found;
        }
    }
}

// Fetch user's current watchlist symbols for star active toggle
$user_watchlist = [];
if ($db) {
    $stmt_w = $db->prepare("SELECT symbol FROM watchlist WHERE user_id = ?");
    $stmt_w->execute([$user_id]);
    $user_watchlist = $stmt_w->fetchAll(PDO::FETCH_COLUMN);
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-heading mb-1">Stock Explorer</h2>
        <p class="text-muted mb-0">Search financial markets, view live quotes, and analyze indicators</p>
      </div>

      <form action="explorer.php" method="GET" class="d-flex gap-2" style="width: 380px; max-width: 100%;">
        <div class="nav-search flex-grow-1" style="width: auto;">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search Ticker (e.g., NVDA, MSFT)..." value="<?php echo htmlspecialchars($search_query); ?>" required>
        </div>
        <button type="submit" class="btn btn-nexora py-2 px-3">Search</button>
      </form>
    </div>

    <!-- Stock Cards Grid -->
    <div class="row g-4" id="explorerGrid">
      <?php foreach ($symbols as $sym): ?>
        <div class="col-md-6 col-lg-4 col-xl-3" id="card-<?php echo $sym; ?>">
          <div class="glass-card h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-secondary text-uppercase px-2 py-1 fs-8 fw-bold"><?php echo $sym; ?></span>
                <button class="btn btn-sm p-0 text-muted watchlist-toggle-btn" onclick="toggleWatchlist('<?php echo $sym; ?>', this)" title="Add to Watchlist">
                  <i class="<?php echo in_array($sym, $user_watchlist) ? 'fas fa-star text-warning' : 'far fa-star'; ?> fs-5"></i>
                </button>
              </div>

              <h5 class="fw-bold text-heading mb-1 stock-name" id="name-<?php echo $sym; ?>"><?php echo $sym; ?> Corp.</h5>
              <div class="d-flex align-items-baseline gap-2 mb-3">
                <span class="fs-3 fw-extrabold text-heading" id="price-<?php echo $sym; ?>">$---.--</span>
                <span class="fs-7 fw-bold" id="change-<?php echo $sym; ?>">--</span>
              </div>

              <div class="row g-2 text-muted fs-7 mb-3 border-top border-bottom py-2" style="border-color: var(--border-color) !important;">
                <div class="col-6">Open: <span class="fw-semibold text-main" id="open-<?php echo $sym; ?>">--</span></div>
                <div class="col-6">High: <span class="fw-semibold text-main" id="high-<?php echo $sym; ?>">--</span></div>
                <div class="col-6">Low: <span class="fw-semibold text-main" id="low-<?php echo $sym; ?>">--</span></div>
                <div class="col-6">Vol: <span class="fw-semibold text-main" id="vol-<?php echo $sym; ?>">--</span></div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-2">
              <a href="stock_detail.php?symbol=<?php echo urlencode($sym); ?>" class="btn btn-outline-nexora btn-sm w-100 py-2">
                <i class="fas fa-chart-line me-1"></i> Technical Analysis
              </a>
              <a href="prediction.php?symbol=<?php echo urlencode($sym); ?>" class="btn btn-nexora btn-sm w-100 py-2">
                <i class="fas fa-wand-magic-sparkles me-1"></i> Predict
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const symbols = <?php echo json_encode($symbols); ?>;
  symbols.forEach(sym => {
    fetch(`api/stock_proxy.php?action=quote&symbol=${sym}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          document.getElementById(`name-${sym}`).innerText = data.company_name;
          document.getElementById(`price-${sym}`).innerText = formatPriceUSDINR(data.current_price);
          
          const changeEl = document.getElementById(`change-${sym}`);
          const sign = data.change >= 0 ? '+' : '';
          changeEl.innerText = `${sign}${data.change.toFixed(2)} (${data.change_pct}%)`;
          changeEl.className = data.change >= 0 ? 'badge-bullish fs-8' : 'badge-bearish fs-8';

          document.getElementById(`open-${sym}`).innerText = formatPriceUSDINR(data.open);
          document.getElementById(`high-${sym}`).innerText = formatPriceUSDINR(data.high);
          document.getElementById(`low-${sym}`).innerText = formatPriceUSDINR(data.low);
          document.getElementById(`vol-${sym}`).innerText = (data.volume / 1000000).toFixed(1) + 'M';
        }
      });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
