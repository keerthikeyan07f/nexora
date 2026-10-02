/**
 * NEXORA - AI Stock Intelligence
 * Application Master Frontend Script
 */

const RUPEE_CONVERSION_RATE = 83;

function formatPriceUSDINR(value) {
  const usd = Number(value) || 0;
  const inr = usd * RUPEE_CONVERSION_RATE;
  return `$${usd.toFixed(2)} | ₹${inr.toFixed(2)}`;
}

document.addEventListener('DOMContentLoaded', () => {
  initThemeToggle();
  initSidebarMobile();
  initBuySignalPopup();
});

function initBuySignalPopup() {
  const buySignalState = {
    shown: new Set(),
    lastKey: ''
  };

  function parseNumericValue(text) {
    if (!text) return null;
    const match = text.match(/[-+]?\d*\.?\d+/);
    return match ? Number(match[0]) : null;
  }

  function createToastContainer() {
    const container = document.getElementById('nexora-buytoast-container');
    if (container) return container;

    const el = document.createElement('div');
    el.id = 'nexora-buytoast-container';
    document.body.appendChild(el);
    return el;
  }

  function showBuySignalToast(symbol, gainPct) {
    const safeSymbol = (symbol || 'Selected stock').toUpperCase();
    const message = `${safeSymbol} is gaining by ${gainPct >= 0 ? '+' : ''}${gainPct.toFixed(2)}%. This is a strong buying opportunity. Consider buying the stock now.`;

    if (window.Notification && Notification.permission === 'granted') {
      new Notification('Buy Signal', {
        body: message,
        tag: `nexora-${safeSymbol}`
      });
    }

    const container = createToastContainer();
    const toast = document.createElement('div');
    toast.className = 'nexora-buytoast';
    toast.innerHTML = `
      <div class="toast-header-row">
        <span class="toast-badge">BUY SIGNAL</span>
        <span class="toast-close">×</span>
      </div>
      <div class="toast-title">${safeSymbol} is bullish</div>
      <div class="toast-message">${message}</div>
    `;

    const closeBtn = toast.querySelector('.toast-close');
    closeBtn.addEventListener('click', () => toast.remove());

    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 50);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 5500);
  }

  function detectBuySignal() {
    const symbolEl = document.getElementById('pSymbol');
    const changeEl = document.getElementById('pChangePct');
    const symbolText = symbolEl ? symbolEl.textContent.trim() : '';
    const rawPct = changeEl ? changeEl.textContent.trim() : '';
    const pctValue = parseNumericValue(rawPct);

    if (!symbolText || pctValue === null || pctValue <= 1) return;

    const key = `${symbolText}-${pctValue}`;
    if (buySignalState.lastKey === key) return;

    buySignalState.lastKey = key;
    if (buySignalState.shown.has(symbolText)) return;

    buySignalState.shown.add(symbolText);
    showBuySignalToast(symbolText, pctValue);
  }

  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission().catch(() => {});
  }

  const signalTargets = [
    document.getElementById('pSymbol'),
    document.getElementById('pChangePct')
  ].filter(Boolean);

  if (signalTargets.length > 0) {
    const observer = new MutationObserver(detectBuySignal);
    signalTargets.forEach(target => observer.observe(target, {
      childList: true,
      characterData: true,
      subtree: true
    }));
  }

  detectBuySignal();
}

/**
 * Theme Manager System
 */
function initThemeToggle() {
  const themeToggleBtns = document.querySelectorAll('.theme-toggle-btn');
  const savedTheme = localStorage.getItem('nexora_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);

  themeToggleBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const currentTheme = document.documentElement.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', newTheme);
      localStorage.setItem('nexora_theme', newTheme);
      document.cookie = `nexora_theme=${newTheme}; path=/; max-age=31536000`;
      
      // Update UI button icons if available
      updateThemeIcons(newTheme);
      
      // Post preference to backend if logged in
      fetch('api/theme_toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `theme=${newTheme}`
      }).catch(err => console.log('Theme sync notice:', err));
    });
  });
}

function updateThemeIcons(theme) {
  const icons = document.querySelectorAll('.theme-toggle-btn i');
  icons.forEach(icon => {
    if (theme === 'dark') {
      icon.className = 'fas fa-sun';
    } else {
      icon.className = 'fas fa-moon';
    }
  });
}

/**
 * Mobile Sidebar Toggle
 */
function initSidebarMobile() {
  const toggleBtn = document.getElementById('mobileSidebarToggle');
  const sidebar = document.querySelector('.sidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('mobile-open');
    });
  }
}

/**
 * Technical Indicators Calculators (Client-Side Chart Helper)
 */
const TechnicalIndicators = {
  // Simple Moving Average
  calculateSMA(data, period) {
    const sma = [];
    for (let i = 0; i < data.length; i++) {
      if (i < period - 1) {
        sma.push(null);
      } else {
        let sum = 0;
        for (let j = 0; j < period; j++) {
          sum += data[i - j];
        }
        sma.push(Number((sum / period).toFixed(2)));
      }
    }
    return sma;
  },

  // Exponential Moving Average
  calculateEMA(data, period) {
    const ema = [];
    const k = 2 / (period + 1);
    let prevEMA = null;

    for (let i = 0; i < data.length; i++) {
      if (i < period - 1) {
        ema.push(null);
      } else if (i === period - 1) {
        let sum = 0;
        for (let j = 0; j < period; j++) sum += data[j];
        prevEMA = sum / period;
        ema.push(Number(prevEMA.toFixed(2)));
      } else {
        prevEMA = (data[i] * k) + (prevEMA * (1 - k));
        ema.push(Number(prevEMA.toFixed(2)));
      }
    }
    return ema;
  },

  // Relative Strength Index (RSI - 14)
  calculateRSI(data, period = 14) {
    const rsi = [];
    let gains = 0, losses = 0;

    for (let i = 1; i <= period; i++) {
      const diff = data[i] - data[i - 1];
      if (diff >= 0) gains += diff;
      else losses -= diff;
    }

    let avgGain = gains / period;
    let avgLoss = losses / period;

    for (let i = 0; i < data.length; i++) {
      if (i < period) {
        rsi.push(null);
      } else {
        if (i > period) {
          const diff = data[i] - data[i - 1];
          const gain = diff >= 0 ? diff : 0;
          const loss = diff < 0 ? -diff : 0;
          avgGain = (avgGain * (period - 1) + gain) / period;
          avgLoss = (avgLoss * (period - 1) + loss) / period;
        }
        const rs = avgLoss === 0 ? 100 : avgGain / avgLoss;
        const val = 100 - (100 / (1 + rs));
        rsi.push(Number(val.toFixed(2)));
      }
    }
    return rsi;
  },

  // MACD (12, 26, 9)
  calculateMACD(data) {
    const ema12 = this.calculateEMA(data, 12);
    const ema26 = this.calculateEMA(data, 26);
    const macdLine = [];

    for (let i = 0; i < data.length; i++) {
      if (ema12[i] !== null && ema26[i] !== null) {
        macdLine.push(ema12[i] - ema26[i]);
      } else {
        macdLine.push(null);
      }
    }

    const validMacd = macdLine.filter(val => val !== null);
    const signalValid = this.calculateEMA(validMacd, 9);
    
    const signalLine = [];
    let validIdx = 0;
    for (let i = 0; i < data.length; i++) {
      if (macdLine[i] !== null) {
        signalLine.push(signalValid[validIdx] !== undefined ? signalValid[validIdx] : null);
        validIdx++;
      } else {
        signalLine.push(null);
      }
    }

    const histogram = [];
    for (let i = 0; i < data.length; i++) {
      if (macdLine[i] !== null && signalLine[i] !== null) {
        histogram.push(Number((macdLine[i] - signalLine[i]).toFixed(2)));
      } else {
        histogram.push(null);
      }
    }

    return { macdLine, signalLine, histogram };
  }
};

/**
 * Watchlist AJAX Manager
 */
function toggleWatchlist(symbol, btnElement) {
  fetch('api/watchlist_action.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `symbol=${encodeURIComponent(symbol)}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'added') {
      btnElement.classList.add('active');
      btnElement.innerHTML = '<i class="fas fa-star text-warning"></i> In Watchlist';
    } else if (data.status === 'removed') {
      btnElement.classList.remove('active');
      btnElement.innerHTML = '<i class="far fa-star"></i> Add to Watchlist';
    }
  })
  .catch(err => console.error('Watchlist action error:', err));
}
/* =========================================
   NEXORA - PROFESSIONAL ANIMATIONS
   ========================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* -----------------------------------------
       Stagger dashboard cards
       ----------------------------------------- */

    const cards = document.querySelectorAll(
        ".card, .stat-card, .metric-card, .dashboard-card"
    );

    cards.forEach(function (card, index) {
        card.style.animationDelay = (index * 0.08) + "s";
    });


    /* -----------------------------------------
       Button click animation
       ----------------------------------------- */

    document.querySelectorAll("button, .btn").forEach(function (button) {

        button.addEventListener("click", function () {

            button.classList.add("nexora-click");

            setTimeout(function () {
                button.classList.remove("nexora-click");
            }, 250);

        });

    });


    /* -----------------------------------------
       Watchlist star animation
       ----------------------------------------- */

    document.querySelectorAll(".watchlist-star").forEach(function (star) {

        star.addEventListener("click", function () {

            star.classList.toggle("active");

        });

    });


    /* -----------------------------------------
       AI loading helper
       ----------------------------------------- */

    window.showAILoading = function (element) {

        if (!element) return;

        element.innerHTML = `
            <span class="ai-loading">
                <span></span>
                <span></span>
                <span></span>
                <strong>AI is analyzing...</strong>
            </span>
        `;

    };


    /* -----------------------------------------
       Number counter animation
       ----------------------------------------- */

    window.nexoraCounter = function (element, target, duration = 1000) {

        if (!element) return;

        const start = 0;
        const startTime = performance.now();

        function update(currentTime) {

            const progress = Math.min(
                (currentTime - startTime) / duration,
                1
            );

            const value = Math.floor(
                start + (target - start) * progress
            );

            element.textContent = value.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            }

        }

        requestAnimationFrame(update);
    };


    /* -----------------------------------------
       Fade-in when elements enter screen
       ----------------------------------------- */

    const observer = new IntersectionObserver(
        function (entries) {

            entries.forEach(function (entry) {

                if (entry.isIntersecting) {

                    entry.target.classList.add(
                        "nexora-visible"
                    );

                }

            });

        },
        {
            threshold: 0.1
        }
    );


    document.querySelectorAll(
        ".chart-container, .dashboard-card"
    ).forEach(function (element) {

        observer.observe(element);

    });

});