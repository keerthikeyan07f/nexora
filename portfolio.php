<?php
define('PAGE_TITLE', 'Virtual Portfolio');
require_once __DIR__ . '/config/config.php';
require_login();

$user_id = $_SESSION['user_id'];
$db = getDBConnection();

$holdings = [];
$transactions = [];

if ($db) {
    // Fetch user virtual holdings
    $stmt_h = $db->prepare("SELECT * FROM portfolio WHERE user_id = ? ORDER BY symbol ASC");
    $stmt_h->execute([$user_id]);
    $holdings = $stmt_h->fetchAll();

    // Fetch transactions history
    $stmt_tx = $db->prepare("SELECT * FROM portfolio_transactions WHERE user_id = ? ORDER BY transaction_date DESC LIMIT 10");
    $stmt_tx->execute([$user_id]);
    $transactions = $stmt_tx->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <!-- Page Header & Action Buttons -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
      <div>
        <div class="d-flex align-items-center gap-2">
          <h2 class="fw-bold text-heading mb-0"><i class="fas fa-wallet text-cyan me-2"></i> Virtual Portfolio Sandbox</h2>
          <span class="badge bg-warning text-dark font-monospace fw-bold fs-8">SIMULATED MARKET</span>
        </div>
        <p class="text-muted mb-0 mt-1">Execute virtual Buy/Sell positions to test strategy performance risk-free</p>
      </div>

      <button class="btn btn-nexora" data-bs-toggle="modal" data-bs-target="#tradeModal">
        <i class="fas fa-exchange-alt me-2"></i> Execute Virtual Trade
      </button>
    </div>

    <!-- Portfolio Summary Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="glass-card text-center py-3">
          <span class="text-muted fw-bold fs-7 d-block uppercase mb-1">Total Current Valuation</span>
          <h3 class="fw-extrabold text-heading mb-0" id="portTotalVal">$0.00</h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="glass-card text-center py-3">
          <span class="text-muted fw-bold fs-7 d-block uppercase mb-1">Total Invested Cost</span>
          <h3 class="fw-extrabold text-heading mb-0" id="portTotalCost">$0.00</h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="glass-card text-center py-3">
          <span class="text-muted fw-bold fs-7 d-block uppercase mb-1">Simulated Profit / Loss</span>
          <h3 class="fw-extrabold mb-0" id="portTotalPL">$0.00</h3>
          <span class="fs-8 fw-bold" id="portTotalPLPct">0.00%</span>
        </div>
      </div>
    </div>

    <!-- Active Holdings Table -->
    <div class="glass-card mb-4">
      <h4 class="fw-bold text-heading mb-3"><i class="fas fa-layer-group text-info me-2"></i> Active Virtual Holdings</h4>

      <?php if (empty($holdings)): ?>
        <div class="empty-state py-4">
          <i class="fas fa-folder-open"></i>
          <p class="mb-0">No active holdings in your virtual portfolio.</p>
          <button class="btn btn-outline-nexora btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#tradeModal">Execute First Trade</button>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table custom-table mb-0">
            <thead>
              <tr>
                <th>Symbol</th>
                <th>Quantity</th>
                <th>Avg Buy Price</th>
                <th>Current Price</th>
                <th>Market Value</th>
                <th>Unrealized P&L</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($holdings as $h): ?>
                <tr id="holdingRow-<?php echo htmlspecialchars($h['symbol']); ?>">
                  <td class="fw-bold text-heading fs-6">
                    <a href="stock_detail.php?symbol=<?php echo urlencode($h['symbol']); ?>" class="text-decoration-none text-heading">
                      <?php echo htmlspecialchars($h['symbol']); ?>
                    </a>
                  </td>
                  <td class="fw-semibold text-main"><?php echo number_format($h['quantity'], 2); ?></td>
                  <td class="text-muted">$<?php echo number_format($h['avg_buy_price'], 2); ?></td>
                  <td class="fw-bold text-heading" id="curP-<?php echo htmlspecialchars($h['symbol']); ?>">$---.--</td>
                  <td class="fw-bold text-heading" id="mVal-<?php echo htmlspecialchars($h['symbol']); ?>">$---.--</td>
                  <td id="pl-<?php echo htmlspecialchars($h['symbol']); ?>">--</td>
                  <td>
                    <button class="btn btn-sm btn-outline-nexora py-1 px-2 fs-8" onclick="openTradeFor('<?php echo htmlspecialchars($h['symbol']); ?>', 'SELL', <?php echo $h['quantity']; ?>)">
                      <i class="fas fa-minus-circle me-1"></i> Sell
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- Transaction History Table -->
    <div class="glass-card">
      <h4 class="fw-bold text-heading mb-3"><i class="fas fa-list-check text-purple me-2"></i> Trade Transaction History</h4>

      <?php if (empty($transactions)): ?>
        <p class="text-muted fs-7 mb-0">No recorded portfolio transactions.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table custom-table mb-0">
            <thead>
              <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Symbol</th>
                <th>Quantity</th>
                <th>Execution Price</th>
                <th>Total Value</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($transactions as $tx): ?>
                <tr>
                  <td class="fs-7 text-muted"><?php echo date('M d, Y H:i', strtotime($tx['transaction_date'])); ?></td>
                  <td>
                    <span class="<?php echo $tx['transaction_type'] === 'BUY' ? 'badge-bullish' : 'badge-bearish'; ?> fs-8">
                      <?php echo $tx['transaction_type']; ?>
                    </span>
                  </td>
                  <td class="fw-bold text-heading"><?php echo htmlspecialchars($tx['symbol']); ?></td>
                  <td><?php echo number_format($tx['quantity'], 2); ?></td>
                  <td>$<?php echo number_format($tx['price'], 2); ?></td>
                  <td class="fw-bold text-heading">$<?php echo number_format($tx['total_amount'], 2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Virtual Trade Modal -->
<div class="modal fade" id="tradeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content glass-card border-0 shadow-lg" style="background: var(--bg-secondary);">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-heading"><i class="fas fa-exchange-alt text-cyan me-2"></i> Virtual Trade Execution</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="tradeForm">
          <div class="mb-3">
            <label class="form-label text-muted fw-semibold fs-7">Transaction Type</label>
            <div class="d-flex gap-2">
              <input type="radio" class="btn-check" name="trade_type" id="typeBuy" value="BUY" checked>
              <label class="btn btn-outline-success w-50 fw-bold" for="typeBuy">Virtual BUY</label>

              <input type="radio" class="btn-check" name="trade_type" id="typeSell" value="SELL">
              <label class="btn btn-outline-danger w-50 fw-bold" for="typeSell">Virtual SELL</label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-muted fw-semibold fs-7">Stock Symbol</label>
            <input type="text" id="tradeSymbol" class="form-control border-0 text-main font-monospace fw-bold" style="background: var(--bg-input);" placeholder="e.g. AAPL" required>
          </div>

          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label text-muted fw-semibold fs-7">Quantity</label>
              <input type="number" step="0.01" id="tradeQty" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="10.0" required>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label text-muted fw-semibold fs-7">Execution Price ($)</label>
              <input type="number" step="0.01" id="tradePrice" class="form-control border-0 text-main" style="background: var(--bg-input);" placeholder="185.50" required>
            </div>
          </div>

          <button type="button" class="btn btn-nexora w-100 py-3 fw-bold mt-2" onclick="executeTradeSubmit()">
            <i class="fas fa-check-circle me-1"></i> Submit Trade Position
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const holdingsData = <?php echo json_encode($holdings); ?>;

document.addEventListener('DOMContentLoaded', () => {
  calculatePortfolioValuations();
});

function calculatePortfolioValuations() {
  let grandTotalValue = 0;
  let grandTotalCost = 0;

  let remaining = holdingsData.length;
  if (remaining === 0) return;

  holdingsData.forEach(h => {
    fetch(`api/stock_proxy.php?action=quote&symbol=${h.symbol}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          const curPrice = data.current_price;
          const mVal = h.quantity * curPrice;
          const cost = h.quantity * h.avg_buy_price;
          const pl = mVal - cost;
          const plPct = cost > 0 ? (pl / cost) * 100 : 0;

          grandTotalValue += mVal;
          grandTotalCost += cost;

          document.getElementById(`curP-${h.symbol}`).innerText = `$${curPrice.toFixed(2)}`;
          document.getElementById(`mVal-${h.symbol}`).innerText = `$${mVal.toFixed(2)}`;

          const plEl = document.getElementById(`pl-${h.symbol}`);
          const sign = pl >= 0 ? '+' : '';
          plEl.innerText = `${sign}$${pl.toFixed(2)} (${plPct.toFixed(2)}%)`;
          plEl.className = pl >= 0 ? 'badge-bullish fs-8' : 'badge-bearish fs-8';
        }

        remaining--;
        if (remaining === 0) {
          const totalPL = grandTotalValue - grandTotalCost;
          const totalPLPct = grandTotalCost > 0 ? (totalPL / grandTotalCost) * 100 : 0;

          document.getElementById('portTotalVal').innerText = `$${grandTotalValue.toFixed(2)}`;
          document.getElementById('portTotalCost').innerText = `$${grandTotalCost.toFixed(2)}`;
          
          const grandPLEl = document.getElementById('portTotalPL');
          const grandPLPctEl = document.getElementById('portTotalPLPct');
          const sign = totalPL >= 0 ? '+' : '';

          grandPLEl.innerText = `${sign}$${totalPL.toFixed(2)}`;
          grandPLEl.className = totalPL >= 0 ? 'fw-extrabold text-success mb-0' : 'fw-extrabold text-danger mb-0';
          
          grandPLPctEl.innerText = `${sign}${totalPLPct.toFixed(2)}%`;
          grandPLPctEl.className = totalPL >= 0 ? 'badge-bullish fs-8' : 'badge-bearish fs-8';
        }
      });
  });
}

function openTradeFor(symbol, type, qty) {
  document.getElementById('tradeSymbol').value = symbol;
  document.getElementById('tradeQty').value = qty;
  if (type === 'BUY') document.getElementById('typeBuy').checked = true;
  else document.getElementById('typeSell').checked = true;
  
  const modal = new bootstrap.Modal(document.getElementById('tradeModal'));
  modal.show();
}

function executeTradeSubmit() {
  const symbol = document.getElementById('tradeSymbol').value.trim();
  const action = document.querySelector('input[name="trade_type"]:checked').value;
  const quantity = document.getElementById('tradeQty').value;
  const price = document.getElementById('tradePrice').value;

  fetch('api/portfolio_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `symbol=${encodeURIComponent(symbol)}&action=${action}&quantity=${quantity}&price=${price}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      location.reload();
    } else {
      alert(data.message);
    }
  });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
