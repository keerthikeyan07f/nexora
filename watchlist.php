<?php
define('PAGE_TITLE', 'Stock Watchlist');
require_once __DIR__ . '/config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$db = getDBConnection();

$watchlist = [];
if ($db) {
    $stmt = $db->prepare("SELECT * FROM watchlist WHERE user_id = ? ORDER BY added_at DESC");
    $stmt->execute([$user_id]);
    $watchlist = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <h2 class="fw-bold text-heading mb-1"><i class="fas fa-star text-warning me-2"></i> Custom Watchlist</h2>
        <p class="text-muted mb-0">Monitor price movements and quick access to technical indicators</p>
      </div>

      <form action="watchlist.php" method="POST" class="d-flex gap-2">
        <input type="text" id="addWatchInput" class="form-control border-0 text-main font-monospace" style="background: var(--bg-input); width: 220px;" placeholder="Ticker (e.g. TSLA)...">
        <button type="button" class="btn btn-nexora py-2 px-3" onclick="manualAddWatchlist()">
          <i class="fas fa-plus me-1"></i> Add Stock
        </button>
      </form>
    </div>

    <?php if (empty($watchlist)): ?>
      <div class="glass-card text-center py-5">
        <div class="empty-state">
          <i class="far fa-star"></i>
          <h4 class="fw-bold text-heading mb-2">Watchlist Empty</h4>
          <p class="text-muted mb-3">Add stocks to track their real-time performance and AI prediction signals.</p>
          <a href="explorer.php" class="btn btn-nexora">Browse Markets</a>
        </div>
      </div>
    <?php else: ?>

      <div class="glass-card">
        <div class="table-responsive">
          <table class="table custom-table mb-0">
            <thead>
              <tr>
                <th>Symbol</th>
                <th>Company Name</th>
                <th>Current Price</th>
                <th>Day's Change</th>
                <th>Volume</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="watchlistTableBody">
              <?php foreach ($watchlist as $item): ?>
                <tr id="row-<?php echo htmlspecialchars($item['symbol']); ?>">
                  <td class="fw-bold text-heading fs-6">
                    <a href="stock_detail.php?symbol=<?php echo urlencode($item['symbol']); ?>" class="text-decoration-none text-heading">
                      <?php echo htmlspecialchars($item['symbol']); ?>
                    </a>
                  </td>
                  <td class="text-muted" id="wName-<?php echo htmlspecialchars($item['symbol']); ?>">Loading...</td>
                  <td class="fw-bold text-heading" id="wPrice-<?php echo htmlspecialchars($item['symbol']); ?>">$---.--</td>
                  <td id="wChange-<?php echo htmlspecialchars($item['symbol']); ?>">--</td>
                  <td class="text-muted fs-7" id="wVol-<?php echo htmlspecialchars($item['symbol']); ?>">--</td>
                  <td>
                    <div class="d-flex gap-2">
                      <a href="stock_detail.php?symbol=<?php echo urlencode($item['symbol']); ?>" class="btn btn-outline-nexora btn-sm py-1 px-2 fs-8">
                        <i class="fas fa-chart-area me-1"></i> Analysis
                      </a>
                      <a href="prediction.php?symbol=<?php echo urlencode($item['symbol']); ?>" class="btn btn-nexora btn-sm py-1 px-2 fs-8">
                        <i class="fas fa-wand-magic-sparkles me-1"></i> Predict
                      </a>
                      <button class="btn btn-sm btn-outline-danger py-1 px-2 fs-8" onclick="removeWatchlist('<?php echo htmlspecialchars($item['symbol']); ?>')">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php endif; ?>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const watchlist = <?php echo json_encode(array_column($watchlist, 'symbol')); ?>;
  watchlist.forEach(sym => {
    fetch(`api/stock_proxy.php?action=quote&symbol=${sym}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          document.getElementById(`wName-${sym}`).innerText = data.company_name;
          document.getElementById(`wPrice-${sym}`).innerText = `$${data.current_price.toFixed(2)}`;
          
          const sign = data.change >= 0 ? '+' : '';
          const changeEl = document.getElementById(`wChange-${sym}`);
          changeEl.innerText = `${sign}$${data.change.toFixed(2)} (${data.change_pct}%)`;
          changeEl.className = data.change >= 0 ? 'badge-bullish fs-8' : 'badge-bearish fs-8';

          document.getElementById(`wVol-${sym}`).innerText = (data.volume / 1000000).toFixed(1) + 'M';
        }
      });
  });
});

function manualAddWatchlist() {
  const input = document.getElementById('addWatchInput');
  const symbol = input.value.trim();
  if (symbol) {
    toggleWatchlist(symbol, input);
    setTimeout(() => location.reload(), 300);
  }
}

function removeWatchlist(symbol) {
  fetch('api/watchlist_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `symbol=${encodeURIComponent(symbol)}`
  })
  .then(res => res.json())
  .then(data => {
    const row = document.getElementById(`row-${symbol}`);
    if (row) row.remove();
  });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
