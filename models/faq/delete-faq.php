<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/connection.php';

// Authorization Guard check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}

// Ensure it is accessed via a GET parameter ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['form_error_message'] = "Invalid FAQ reference ID.";
    header("Location: ../../admin-dashboard.php?page=faqs");
    exit();
}

try {
    global $conn;
    
    // Execute targeted delete query
    $query = "DELETE FROM faqs WHERE id = :id LIMIT 1";
    $stmt = $conn->prepare($query);
    $isDeleted = $stmt->execute(['id' => $id]);

    if ($isDeleted && $stmt->rowCount() > 0) {
        $_SESSION['form_success_message'] = "FAQ item has been permanently deleted.";
    } else {
        $_SESSION['form_error_message'] = "Failed to delete FAQ. It may have already been removed.";
    }
} catch (PDOException $e) {
    error_log("Database error in delete_faq.php: " . $e->getMessage());
    $_SESSION['form_error_message'] = "Internal database structural error handling deletion.";
}

// Redirect cleanly back onto the active FAQ workspace page view loop
header("Location: ../../admin-dashboard.php?page=faqs");
exit();