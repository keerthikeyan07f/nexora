<?php
define('PAGE_TITLE', 'Stock Technical Analysis');
require_once __DIR__ . '/config/config.php';
require_login();

$symbol = strtoupper(trim($_GET['symbol'] ?? 'AAPL'));
$user_id = $_SESSION['user_id'];
$db = getDBConnection();

$in_watchlist = false;
if ($db) {
    $stmt = $db->prepare("SELECT id FROM watchlist WHERE user_id = ? AND symbol = ?");
    $stmt->execute([$user_id, $symbol]);
    $in_watchlist = (bool)$stmt->fetch();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-body">
    <!-- Stock Title Header Banner -->
    <div class="glass-card mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="d-flex align-items-center gap-3">
            <h1 class="display-6 fw-extrabold text-heading mb-0" id="detailSymbol"><?php echo htmlspecialchars($symbol); ?></h1>
            <span class="badge bg-primary text-uppercase px-3 py-2 fs-7 fw-bold" id="detailSector">Technology</span>
            <button class="btn btn-outline-nexora btn-sm py-1 px-3 fs-7 <?php echo $in_watchlist ? 'active' : ''; ?>" onclick="toggleWatchlist('<?php echo $symbol; ?>', this)" id="detailWatchlistBtn">
              <i class="<?php echo $in_watchlist ? 'fas fa-star text-warning' : 'far fa-star'; ?> me-1"></i>
              <?php echo $in_watchlist ? 'In Watchlist' : 'Add to Watchlist'; ?>
            </button>
          </div>
          <p class="text-muted mb-0 fs-6 mt-1" id="detailCompanyName">Loading company statistics...</p>
        </div>

        <div class="text-end">
          <div class="display-6 fw-extrabold text-heading mb-0" id="detailPrice">$---.--</div>
          <div class="fs-6 fw-bold" id="detailChange">--</div>
        </div>
      </div>

      <!-- Quick Metrics Grid -->
      <div class="row g-3 text-muted fs-7 border-top mt-3 pt-3" style="border-color: var(--border-color) !important;">
        <div class="col-6 col-md-2">Open: <span class="fw-bold text-main d-block fs-6" id="dOpen">--</span></div>
        <div class="col-6 col-md-2">High: <span class="fw-bold text-main d-block fs-6" id="dHigh">--</span></div>
        <div class="col-6 col-md-2">Low: <span class="fw-bold text-main d-block fs-6" id="dLow">--</span></div>
        <div class="col-6 col-md-2">Previous Close: <span class="fw-bold text-main d-block fs-6" id="dPrevClose">--</span></div>
        <div class="col-6 col-md-2">Volume: <span class="fw-bold text-main d-block fs-6" id="dVol">--</span></div>
        <div class="col-6 col-md-2">
          <a href="prediction.php?symbol=<?php echo urlencode($symbol); ?>" class="btn btn-nexora btn-sm w-100 py-2">
            <i class="fas fa-wand-magic-sparkles me-1"></i> AI Forecast
          </a>
        </div>
      </div>
    </div>

    <!-- Indicator Overlay Controls & Time Filters -->
    <div class="glass-card mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <!-- Technical Indicator Toggles -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="text-muted fw-bold fs-7 me-2"><i class="fas fa-sliders me-1 text-info"></i> Indicators:</span>
          <button class="btn btn-outline-nexora btn-sm py-1 px-3 active" id="toggleSMA" onclick="toggleOverlay('sma')">20-SMA</button>
          <button class="btn btn-outline-nexora btn-sm py-1 px-3 active" id="toggleEMA" onclick="toggleOverlay('ema')">50-EMA</button>
          <button class="btn btn-outline-nexora btn-sm py-1 px-3 active" id="toggleRSI" onclick="toggleOverlay('rsi')">RSI (14)</button>
          <button class="btn btn-outline-nexora btn-sm py-1 px-3 active" id="toggleMACD" onclick="toggleOverlay('macd')">MACD</button>
        </div>

        <!-- Time Range Selector -->
        <div class="d-flex gap-1" id="detailTimeFilter">
          <button class="time-btn" onclick="changePeriod('1m', this)">1M</button>
          <button class="time-btn" onclick="changePeriod('3m', this)">3M</button>
          <button class="time-btn active" onclick="changePeriod('6m', this)">6M</button>
          <button class="time-btn" onclick="changePeriod('1y', this)">1Y</button>
          <button class="time-btn" onclick="changePeriod('5y', this)">5Y</button>
        </div>
      </div>
    </div>

    <!-- Main Price & Technical Charts Stack -->
    <div class="row g-4 mb-4">
      <div class="col-12">
        <div class="glass-card">
          <h5 class="fw-bold text-heading mb-3"><i class="fas fa-chart-line text-cyan me-2"></i> Price Chart & Moving Averages</h5>
          <div style="height: 380px; position: relative;">
            <canvas id="priceChartCanvas"></canvas>
          </div>
        </div>
      </div>

      <!-- Volume Bar Chart -->
      <div class="col-12">
        <div class="glass-card">
          <h5 class="fw-bold text-heading mb-2"><i class="fas fa-bar-chart text-purple me-2"></i> Trading Volume</h5>
          <div style="height: 140px; position: relative;">
            <canvas id="volumeChartCanvas"></canvas>
          </div>
        </div>
      </div>

      <!-- RSI Sub-Chart -->
      <div class="col-12" id="rsiContainer">
        <div class="glass-card">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold text-heading mb-0"><i class="fas fa-wave-square text-emerald me-2"></i> Relative Strength Index (RSI - 14)</h5>
            <span class="fs-7 text-muted">Overbought: &gt;70 | Oversold: &lt;30</span>
          </div>
          <div style="height: 160px; position: relative;">
            <canvas id="rsiChartCanvas"></canvas>
          </div>
        </div>
      </div>

      <!-- MACD Sub-Chart -->
      <div class="col-12" id="macdContainer">
        <div class="glass-card">
          <h5 class="fw-bold text-heading mb-2"><i class="fas fa-chart-bar text-amber me-2"></i> MACD (12, 26, 9)</h5>
          <div style="height: 180px; position: relative;">
            <canvas id="macdChartCanvas"></canvas>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
const currentSymbol = "<?php echo $symbol; ?>";
let activePeriod = "6m";
let rawHistoryData = [];

let priceChart = null;
let volumeChart = null;
let rsiChart = null;
let macdChart = null;

let showSMA = true;
let showEMA = true;

document.addEventListener('DOMContentLoaded', () => {
  fetchStockDetails(activePeriod);
});

function changePeriod(period, btn) {
  activePeriod = period;
  document.querySelectorAll('#detailTimeFilter .time-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  fetchStockDetails(period);
}

function toggleOverlay(type) {
  if (type === 'sma') {
    showSMA = !showSMA;
    document.getElementById('toggleSMA').classList.toggle('active', showSMA);
  } else if (type === 'ema') {
    showEMA = !showEMA;
    document.getElementById('toggleEMA').classList.toggle('active', showEMA);
  } else if (type === 'rsi') {
    const el = document.getElementById('rsiContainer');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    document.getElementById('toggleRSI').classList.toggle('active');
  } else if (type === 'macd') {
    const el = document.getElementById('macdContainer');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    document.getElementById('toggleMACD').classList.toggle('active');
  }
  renderCharts();
}

function fetchStockDetails(period) {
  fetch(`api/stock_proxy.php?action=details&symbol=${currentSymbol}&period=${period}`)
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        document.getElementById('detailCompanyName').innerText = data.company_name;
        document.getElementById('detailSector').innerText = data.sector;
        document.getElementById('detailPrice').innerText = formatPriceUSDINR(data.current_price);
        
        const sign = data.change >= 0 ? '+' : '';
        const changeEl = document.getElementById('detailChange');
        changeEl.innerText = `${sign}$${data.change.toFixed(2)} (${data.change_pct}%)`;
        changeEl.className = data.change >= 0 ? 'badge-bullish fs-6' : 'badge-bearish fs-6';

        document.getElementById('dOpen').innerText = formatPriceUSDINR(data.open);
        document.getElementById('dHigh').innerText = formatPriceUSDINR(data.high);
        document.getElementById('dLow').innerText = formatPriceUSDINR(data.low);
        document.getElementById('dPrevClose').innerText = formatPriceUSDINR(data.previous_close);
        document.getElementById('dVol').innerText = (data.volume / 1000000).toFixed(2) + 'M';

        rawHistoryData = data.history;
        renderCharts();
      }
    });
}

function renderCharts() {
  if (!rawHistoryData || rawHistoryData.length === 0) return;

  const dates  = rawHistoryData.map(h => h.date);
  const prices = rawHistoryData.map(h => h.close);
  const vols   = rawHistoryData.map(h => h.volume);

  const sma20 = TechnicalIndicators.calculateSMA(prices, 20);
  const ema50 = TechnicalIndicators.calculateEMA(prices, 50);
  const rsi14 = TechnicalIndicators.calculateRSI(prices, 14);
  const macdData = TechnicalIndicators.calculateMACD(prices);

  // 1. Price Chart
  const ctxPrice = document.getElementById('priceChartCanvas').getContext('2d');
  if (priceChart) priceChart.destroy();

  const datasets = [{
    label: `${currentSymbol} Close Price`,
    data: prices,
    borderColor: '#00f2fe',
    borderWidth: 2,
    pointRadius: 0,
    tension: 0.2
  }];

  if (showSMA) {
    datasets.push({
      label: '20-Day SMA',
      data: sma20,
      borderColor: '#ffab00',
      borderWidth: 1.5,
      borderDash: [4, 4],
      pointRadius: 0
    });
  }

  if (showEMA) {
    datasets.push({
      label: '50-Day EMA',
      data: ema50,
      borderColor: '#8e2de2',
      borderWidth: 1.5,
      pointRadius: 0
    });
  }

  priceChart = new Chart(ctxPrice, {
    type: 'line',
    data: { labels: dates, datasets: datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#8a99ad' } } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#8a99ad', maxTicksLimit: 10 } },
        y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#8a99ad' } }
      }
    }
  });

  // 2. Volume Chart
  const ctxVol = document.getElementById('volumeChartCanvas').getContext('2d');
  if (volumeChart) volumeChart.destroy();
  volumeChart = new Chart(ctxVol, {
    type: 'bar',
    data: {
      labels: dates,
      datasets: [{
        label: 'Volume',
        data: vols,
        backgroundColor: rawHistoryData.map(h => h.close >= h.open ? 'rgba(0, 230, 118, 0.5)' : 'rgba(255, 51, 102, 0.5)')
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { display: false } },
        y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#8a99ad', maxTicksLimit: 3 } }
      }
    }
  });

  // 3. RSI Chart
  const ctxRSI = document.getElementById('rsiChartCanvas').getContext('2d');
  if (rsiChart) rsiChart.destroy();
  rsiChart = new Chart(ctxRSI, {
    type: 'line',
    data: {
      labels: dates,
      datasets: [{
        label: 'RSI (14)',
        data: rsi14,
        borderColor: '#00e676',
        borderWidth: 1.5,
        pointRadius: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#8a99ad', maxTicksLimit: 10 } },
        y: { min: 0, max: 100, grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#8a99ad', stepSize: 30 } }
      }
    }
  });

  // 4. MACD Chart
  const ctxMACD = document.getElementById('macdChartCanvas').getContext('2d');
  if (macdChart) macdChart.destroy();
  macdChart = new Chart(ctxMACD, {
    type: 'bar',
    data: {
      labels: dates,
      datasets: [
        {
          type: 'line',
          label: 'MACD Line',
          data: macdData.macdLine,
          borderColor: '#4facfe',
          borderWidth: 1.5,
          pointRadius: 0
        },
        {
          type: 'line',
          label: 'Signal Line',
          data: macdData.signalLine,
          borderColor: '#ffab00',
          borderWidth: 1.5,
          pointRadius: 0
        },
        {
          label: 'Histogram',
          data: macdData.histogram,
          backgroundColor: macdData.histogram.map(val => val >= 0 ? 'rgba(0, 230, 118, 0.6)' : 'rgba(255, 51, 102, 0.6)')
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { labels: { color: '#8a99ad' } } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#8a99ad', maxTicksLimit: 10 } },
        y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#8a99ad' } }
      }
    }
  });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
