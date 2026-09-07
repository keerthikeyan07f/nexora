<?php
require_once __DIR__ . '/../config/config.php';
$current_theme = get_user_theme();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($current_theme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' | ' . APP_NAME : APP_NAME . ' - ' . APP_TAGLINE; ?></title>
  <meta name="description" content="NEXORA AI-Powered Stock Market Prediction Dashboard using TensorFlow LSTM Neural Networks.">
  
  <!-- Favicon -->
  <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
  
  <!-- Custom Master Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
