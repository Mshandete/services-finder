<?php

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// SESSION HELPERS
// ===============================

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user role
function currentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Redirect helper
function redirect($path) {
    header("Location: $path");
    exit;
}