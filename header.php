<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Prince Umrao - Verified Portfolio & Certificate Validation System">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Prince Umrao - Portfolio' ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Loading Screen -->
<div id="loader" class="loader-overlay">
    <div class="loader-content">
        <div class="loader-ring"></div>
        <p class="loader-text">Loading Portfolio</p>
    </div>
</div>

<!-- Particle Canvas Background -->
<canvas id="particleCanvas"></canvas>

<!-- Navigation -->
<nav class="main-nav" id="mainNav">
    <div class="nav-inner">
        <a href="index.php" class="nav-logo">
            <i class="fas fa-shield-halved"></i>
            <span>Prince<span class="accent">Verify</span></span>
        </a>
        <div class="nav-links" id="navLinks">
            <a href="index.php" class="nav-link active"><i class="fas fa-home"></i> Home</a>
            <a href="index.php#certificates" class="nav-link"><i class="fas fa-award"></i> Certificates</a>
            <a href="index.php#verify" class="nav-link"><i class="fas fa-search"></i> Verify</a>
            <a href="admin/login.php" class="nav-link"><i class="fas fa-lock"></i> Admin</a>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>