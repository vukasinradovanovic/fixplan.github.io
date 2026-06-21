<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/../functions/auth.php'; // Učitavamo model sa novom funkcijom
require_once __DIR__ . '/../functions/guards.php';

protectRoute(['Admin'], '../../index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['form_error_message'] = "Nevalidan ID korisnika.";
        header("Location: ../../admin-dashboard.php?page=users");
        exit();
    }

    $firstName  = trim($_POST['first_name'] ?? '');
    $lastName   = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $isVerified = (int)($_POST['is_verified'] ?? 0);
    $isLocked   = (int)($_POST['is_locked'] ?? 0);

    if (empty($firstName) || empty($lastName) || empty($email)) {
        $_SESSION['form_error_message'] = "Sva polja su obavezna.";
        header("Location: ../../admin-dashboard.php?page=users");
        exit();
    }

    // Pozivamo izolovanu funkciju iz modela koja obavlja transakciju
    $result = updateUserInlineInDB($id, $firstName, $lastName, $email, $isVerified, $isLocked);

    if ($result['success']) {
        $_SESSION['form_success_message'] = $result['message'];
    } else {
        $_SESSION['form_error_message'] = $result['message'];
    }

    header("Location: ../../admin-dashboard.php?page=users");
    exit();
}