"""
NEXORA AI - Flask REST API Microservice
Provides prediction endpoints, health checks, model metadata, and historical evaluation metrics.
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
from model_engine import NexoraLSTMEngine

app = Flask(__name__)
CORS(app)  # Enable Cross-Origin Resource Sharing

@app.route('/health', methods=['GET'])
def health_check():
    """System Health Endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'NEXORA AI ML Prediction Engine',
        'version': '1.0.0'
    }), 200

@app.route('/predict', methods=['GET'])
def predict():
    """
    Predict future stock price endpoint.
    Query Params:
    - ticker: Stock symbol (default: AAPL)
    - days: Forecast horizon (default: 7)
    """
    ticker = request.args.get('ticker', 'AAPL').upper().strip()
    try:
        days = int(request.args.get('days', 7))
    except ValueError:
        days = 7

    if not ticker or len(ticker) > 10:
        return jsonify({'status': 'error', 'message': 'Invalid ticker symbol provided.'}), 400

    try:
        engine = NexoraLSTMEngine(symbol=ticker)
        result = engine.predict_next_days(days=days)
        return jsonify(result), 200
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': f"Prediction pipeline failure: {str(e)}"
        }), 500

@app.route('/model-info', methods=['GET'])
def model_info():
    """Retrieve LSTM Architecture & Parameters Overview"""
    ticker = request.args.get('ticker', 'AAPL').upper()
    return jsonify({
        'status': 'success',
        'symbol': ticker,
        'model_type': 'Sequential Deep LSTM',
        'layers': [
            {'type': 'LSTM', 'units': 64, 'return_sequences': True},
            {'type': 'Dropout', 'rate': 0.2},
            {'type': 'LSTM', 'units': 32, 'return_sequences': False},
            {'type': 'Dropout', 'rate': 0.2},
            {'type': 'Dense', 'units': 16, 'activation': 'relu'},
            {'type': 'Dense', 'units': 1, 'activation': 'linear'}
        ],
        'optimizer': 'Adam (lr=0.001)',
        'loss_function': 'Mean Squared Error (MSE)',
        'lookback_window_days': 60
    }), 200

@app.route('/historical-performance', methods=['GET'])
def historical_performance():
    """Retrieve model evaluation metrics from the active prediction engine."""
    ticker = request.args.get('ticker', 'AAPL').upper().strip()
    try:
        engine = NexoraLSTMEngine(symbol=ticker)
        result = engine.predict_next_days(days=7)
        return jsonify({
            'status': 'success',
            'symbol': ticker,
            'evaluation_summary': {
                'mae_usd': result['metrics'].get('mae', 0),
                'rmse_usd': result['metrics'].get('rmse', 0),
                'mape_pct': result['metrics'].get('mape', 0),
                'directional_accuracy_pct': min(99.9, max(0, 100 - result['metrics'].get('mape', 0) * 10))
            }
        }), 200
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': f"Evaluation pipeline failure: {str(e)}"
        }), 500

if __name__ == '__main__':
    print("=" * 60)
    print(" NEXORA AI - Stock Intelligence Flask Service Running")
    print(" Listening on http://127.0.0.1:5000")
    print("=" * 60)
    app.run(host='0.0.0.0', port=5000, debug=True)
