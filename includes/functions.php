<?php
/**
 * PetAssist — Reusable PHP Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Calculate age from DOB
 * Returns a human-readable string like "2 years, 3 months"
 */
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $diff = $birthDate->diff($today);

    $years = $diff->y;
    $months = $diff->m;

    if ($years > 0 && $months > 0) {
        return $years . ' year' . ($years > 1 ? 's' : '') . ', ' . $months . ' month' . ($months > 1 ? 's' : '');
    } elseif ($years > 0) {
        return $years . ' year' . ($years > 1 ? 's' : '');
    } elseif ($months > 0) {
        return $months . ' month' . ($months > 1 ? 's' : '');
    } else {
        $days = $diff->d;
        return $days . ' day' . ($days > 1 ? 's' : '');
    }
}

/**
 * Check if today is the pet's birthday (day + month match)
 */
function isBirthday($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    return ($birthDate->format('m-d') === $today->format('m-d'));
}

/**
 * Calculate health status using rule-based scoring
 * Food: Good=3, Normal=2, Low=1
 * Activity: High=3, Medium=2, Low=1
 * Behavior: Happy=3, Normal=2, Aggressive=1
 * Total: 7-9=Healthy, 4-6=Warning, 1-3=Critical
 */
function getHealthStatus($food, $activity, $behavior) {
    $scores = [
        'food' => ['Good' => 3, 'Normal' => 2, 'Low' => 1],
        'activity' => ['High' => 3, 'Medium' => 2, 'Low' => 1],
        'behavior' => ['Happy' => 3, 'Normal' => 2, 'Aggressive' => 1]
    ];

    $total = ($scores['food'][$food] ?? 1) + 
             ($scores['activity'][$activity] ?? 1) + 
             ($scores['behavior'][$behavior] ?? 1);

    if ($total >= 7) return 'Healthy';
    if ($total >= 4) return 'Warning';
    return 'Critical';
}

/**
 * Set a flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Display and clear flash message
 */
function displayFlash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);

        $iconMap = [
            'success' => 'fa-check-circle',
            'error' => 'fa-exclamation-circle',
            'warning' => 'fa-exclamation-triangle',
            'info' => 'fa-info-circle'
        ];
        $icon = $iconMap[$type] ?? 'fa-info-circle';

        return '<div class="alert alert-' . $type . '" id="flash-alert">
            <i class="fas ' . $icon . '"></i>
            <span>' . $message . '</span>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>';
    }
    return '';
}

/**
 * Get vaccination status based on next_due_date
 */
function getVaccinationStatus($nextDueDate) {
    $today = new DateTime('today');
    $due = new DateTime($nextDueDate);
    $diff = $today->diff($due);
    $daysLeft = (int)$due->format('U') - (int)$today->format('U');
    $daysLeft = $daysLeft / 86400;

    if ($daysLeft < 0) return 'Overdue';
    if ($daysLeft <= 30) return 'Due Soon';
    return 'Completed';
}

/**
 * Require login — redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to access this page.');
        redirect('../pages/login.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('error', 'Access denied. Admin privileges required.');
        redirect('../pages/dashboard.php');
    }
}
?>
