# NEXORA - AI-POWERED STOCK MARKET PREDICTION DASHBOARD

**NEXORA** is a production-quality, full-stack financial intelligence application built with a **PHP** web backend, **MySQL** relational database, **Python Flask REST API**, **TensorFlow/Keras LSTM Neural Network**, and an ultra-modern **Bootstrap 5 + Chart.js** dark/light theme frontend.

---

## 🌟 Architecture Overview

```
[ Browser Client (HTML5 / Bootstrap 5 / Chart.js) ]
                      │
                      ▼
        [ PHP Application Backend ]
          │                    │
          ▼                    ▼
[ MySQL Database ]    [ Flask REST API (Python 3.9+) ]
                             │
                             ▼
                [ TensorFlow / Keras LSTM Engine ]
                             │
                             ▼
                     [ yfinance Data ]
```

---

## 📂 Project Structure

```
FINAL YEAR PROJECT/
├── config/
│   └── config.php               # Database connection, auth middleware & global helpers
├── database/
│   └── schema.sql               # MySQL database schema (10 tables + initial seed data)
├── assets/
│   ├── css/
│   │   └── style.css            # Master stylesheet with dark/light theme system & glassmorphism
│   └── js/
│       └── app.js               # Theme manager, indicators calculation, AJAX helpers
├── includes/
│   ├── header.php               # HTML head & CDN declarations
│   ├── footer.php               # Scripts & closing body tags
│   ├── navbar.php               # Top navigation header & user dropdown
│   └── sidebar.php              # Navigation sidebar
├── auth/
│   ├── login.php                # User login page
│   ├── register.php             # User registration page
│   └── logout.php               # Session cleanup & logout handler
├── api/
│   ├── stock_proxy.php          # PHP bridge for yfinance & Flask ML API
│   ├── watchlist_action.php     # AJAX add/remove watchlist handler
│   ├── portfolio_action.php     # AJAX buy/sell virtual trade executor
│   ├── notification_action.php  # AJAX notification reader
│   └── theme_toggle.php         # AJAX theme switcher preference
├── python_ml/
│   ├── app.py                   # Flask REST API microservice (Port 5000)
│   ├── model_engine.py          # yfinance pipeline, MinMaxScaler, LSTM model engine
│   ├── indicators.py           # Technical indicators calculator (SMA, EMA, RSI, MACD)
│   ├── requirements.txt         # Python package dependencies
│   └── models/                  # Directory for saved model weights (.keras) & scalers (.pkl)
├── index.php                    # Landing page
├── dashboard.php                # Market overview dashboard
├── explorer.php                 # Stock explorer & live symbol search
├── stock_detail.php             # Stock technical analysis & indicator charts
├── prediction.php               # AI LSTM prediction dashboard
├── prediction_history.php       # Saved prediction history log
├── watchlist.php                # Watchlist manager
├── portfolio.php                # Virtual trading sandbox & portfolio tracker
├── notifications.php            # User notifications feed
├── settings.php                 # User settings, profile & password management
├── profile.php                  # User profile & account statistics
├── .env.example                 # Environment variables configuration template
└── README.md                    # Documentation & setup instructions
```

---

## 🚀 Setup & Execution Guide

### 1. Database Setup (MySQL / XAMPP)
1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`) or MySQL Command Line.
3. Import the database schema:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   *This creates the `nexora_db` database and populates 10 tables with demo data.*

---

### 2. PHP Web Server Setup
You can host the application using XAMPP or the PHP built-in web server:

**Using PHP Built-in Server (Recommended):**
Open terminal in the project root directory (`d:\FINAL YEAR PROJECT`) and run:
```bash
php -S localhost:8000
```
Then navigate to: `http://localhost:8000`

**Demo Credentials:**
- **Email**: `demo@nexora.ai`
- **Password**: `DemoUser123!`

---

### 3. Python Flask ML Service Setup
1. Open terminal in the `python_ml` directory:
   ```bash
   cd python_ml
   ```
2. Create and activate a Python virtual environment:
   ```bash
   python -m venv venv
   # On Windows PowerShell:
   .\venv\Scripts\Activate.ps1
   # On Git Bash / Linux / macOS:
   source venv/bin/activate
   ```
3. Install required Python packages:
   ```bash
   pip install -r requirements.txt
   ```
4. Start the Flask REST API server:
   ```bash
   python app.py
   ```
   *The Flask API will run on `http://127.0.0.1:5000`.*

---

## 📡 Flask REST API Documentation

| Method | Endpoint | Description | Query Parameters |
| :--- | :--- | :--- | :--- |
| `GET` | `/health` | Health status check | None |
| `GET` | `/predict` | Triggers LSTM inference & return multi-day forecast | `ticker=AAPL`, `days=7` |
| `GET` | `/model-info` | Returns model architecture & layer metadata | `ticker=AAPL` |
| `GET` | `/historical-performance` | Returns MAE, RMSE, MAPE accuracy metrics | `ticker=AAPL` |

---

## 🔒 Security Features
- **SQL Injection Prevention**: All queries use PDO prepared statements with bound parameters.
- **XSS Protection**: HTML output escaping via standard `sanitize()` utility.
- **Password Hashing**: User passwords hashed using `PASSWORD_BCRYPT`.
- **Session Security**: Authenticated route authorization checks and session validation.

---

## 📊 Technical Indicators Implemented
1. **SMA (Simple Moving Average)**: 20-day window.
2. **EMA (Exponential Moving Average)**: 50-day window.
3. **RSI (Relative Strength Index)**: 14-period momentum oscillator.
4. **MACD**: Moving Average Convergence Divergence (12, 26, 9) with Signal Line & Histogram.

---

## 💡 Notes
- The application automatically falls back to embedded neural linear regression if the Flask service is launching or TensorFlow dependencies are preparing.
- AI predictions are generated for educational and analytical purposes.
"# Stock_Market_Prediction" 
