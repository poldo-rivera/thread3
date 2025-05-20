<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Threads Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <script src="modal.js" defer></script>
</head>
<body>
    <div class="d-flex">
        <!-- Vertical Navigation -->
        <nav class="vertical-nav">
            <div class="nav-logo">
                <a href="index.php">
                    <img src="logo.jpg" alt="Logo" class="nav-logo-img">
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link"><i class="bi bi-house-door"></i></a></li>
                <li><a href="explore.php" class="nav-link"><i class="bi bi-search"></i></a></li>
                <li><button class="nav-link create-post-button"><i class="bi bi-plus-square"></i></button></li>
                <li><a href="notifications.php" class="nav-link"><i class="bi bi-heart"></i></a></li>
                <li><a href="profile.php?id=<?php echo getCurrentUserId(); ?>" class="nav-link"><i class="bi bi-person"></i></a></li>
                <li><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i></a></li>
            </ul>
        </nav>
        <!-- Main Content -->
        <main class="main-content">
