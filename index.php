<?php
/**
 * Main Application Index
 * Redirects to dashboard if authenticated, or login page if guest.
 */
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
