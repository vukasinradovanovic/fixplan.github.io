<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/connection.php';
require_once dirname(__DIR__, 1) . '/auth.php';
require_once dirname(__DIR__, 1) . '/functions/guards.php';

protectRoute(['Admin'], '../../index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['form_error_message'] = "Invalid target identity token parameter constraints.";
        header("Location: ../../admin-dashboard.php?page=users");
        exit();
    }

    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $roleId     = (int)($_POST['role_id'] ?? 0);
    $isVerified = (int)($_POST['is_verified'] ?? 0);
    $isLocked   = (int)($_POST['is_locked'] ?? 0);

    if (empty($firstName) || empty($lastName) || empty($email) || $roleId <= 0) {
        $_SESSION['form_error_message'] = "All data inputs including explicit role assignments are required parameters.";
        header("Location: ../../admin-dashboard.php?page=users");
        exit();
    }

    // Call database transactional routine logic processing functions
    $result = updateUserInlineInDB($id, $firstName, $lastName, $email, $isVerified, $isLocked, $roleId);

    if ($result['success']) {
        $_SESSION['form_success_message'] = $result['message'];
    } else {
        $_SESSION['form_error_message'] = $result['message'];
    }

    header("Location: ../../admin-dashboard.php?page=users");
    exit();
}