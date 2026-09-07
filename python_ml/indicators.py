"""
NEXORA AI - Technical Analysis Indicator Utilities in Python
Calculates SMA, EMA, RSI, and MACD on pandas DataFrames.
"""

import pandas as pd
import numpy as np

def calculate_sma(df, period=20, col='Close'):
    """Calculate Simple Moving Average"""
    return df[col].rolling(window=period).mean()

def calculate_ema(df, period=50, col='Close'):
    """Calculate Exponential Moving Average"""
    return df[col].ewm(span=period, adjust=False).mean()

def calculate_rsi(df, period=14, col='Close'):
    """Calculate Relative Strength Index (RSI)"""
    delta = df[col].diff()
    gain = (delta.where(delta > 0, 0)).rolling(window=period).mean()
    loss = (-delta.where(delta < 0, 0)).rolling(window=period).mean()

    rs = gain / loss
    rsi = 100 - (100 / (1 + rs))
    return rsi.fillna(50)

def calculate_macd(df, fast=12, slow=26, signal=9, col='Close'):
    """Calculate MACD Line, Signal Line, and Histogram"""
    exp1 = df[col].ewm(span=fast, adjust=False).mean()
    exp2 = df[col].ewm(span=slow, adjust=False).mean()
    macd_line = exp1 - exp2
    signal_line = macd_line.ewm(span=signal, adjust=False).mean()
    histogram = macd_line - signal_line
    return macd_line, signal_line, histogram
