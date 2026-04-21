<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';

// Determine base path for includes
$isRoot = basename(dirname($_SERVER['SCRIPT_FILENAME'])) !== 'pages';
$basePath = $isRoot ? '.' : '..';
$pagesPath = $isRoot ? 'pages/' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PetAssist — Your smart companion for pet health, vaccinations, and care management.">
    <title>PetAssist — Smart Pet Care</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="<?php echo $basePath; ?>/index.php" class="nav-logo">
                <i class="fas fa-paw"></i>
                <span>PetAssist</span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span class="hamburger"></span>
            </button>

            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo $basePath; ?>/index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>

                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo $basePath; ?>/pages/dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/pets.php" class="nav-link"><i class="fas fa-dog"></i> My Pets</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/vaccinations.php" class="nav-link"><i class="fas fa-syringe"></i> Vaccinations</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/health.php" class="nav-link"><i class="fas fa-heartbeat"></i> Health</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/care-center.php" class="nav-link"><i class="fas fa-hands-helping"></i> Care Center</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/lost-found.php" class="nav-link"><i class="fas fa-search"></i> Lost & Found</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/guide.php" class="nav-link"><i class="fas fa-book"></i> Guide</a></li>

                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo $basePath; ?>/pages/admin.php" class="nav-link nav-admin"><i class="fas fa-shield-alt"></i> Admin</a></li>
                    <?php endif; ?>

                    <li class="nav-user-section">
                        <div class="nav-user">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo sanitize($_SESSION['user_name']); ?></span>
                        </div>
                        <a href="<?php echo $basePath; ?>/pages/logout.php" class="nav-link nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo $basePath; ?>/pages/login.php" class="nav-link"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/register.php" class="nav-link btn-nav-register"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container">
        <?php echo displayFlash(); ?>
    </div>

    <!-- Main Content Begins -->
    <main class="main-content">
