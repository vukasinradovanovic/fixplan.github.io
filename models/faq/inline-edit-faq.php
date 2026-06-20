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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['form_error_message'] = "Invalid FAQ ID reference.";
        header("Location: ../../admin-dashboard.php?page=faqs");
        exit();
    }

    $question     = trim($_POST['question'] ?? '');
    $answer       = trim($_POST['answer'] ?? '');
    $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;

    if (empty($question) || empty($answer)) {
        $_SESSION['form_error_message'] = "Both Question and Answer text fields are strictly required.";
        header("Location: ../../admin-dashboard.php?page=faqs");
        exit();
    }

    try {
        global $conn;
        $query = "UPDATE faqs 
                  SET question = :question, answer = :answer, display_order = :display_order 
                  WHERE id = :id";
                  
        $stmt = $conn->prepare($query);
        $isSaved = $stmt->execute([
            'question'      => $question,
            'answer'        => $answer,
            'display_order' => $displayOrder,
            'id'            => $id
        ]);

        if ($isSaved) {
            $_SESSION['form_success_message'] = "FAQ updated successfully directly from the table row.";
        } else {
            $_SESSION['form_error_message'] = "Failed to update database records for this FAQ.";
        }
    } catch (PDOException $e) {
        error_log("Database error in inline_edit_faq.php: " . $e->getMessage());
        $_SESSION['form_error_message'] = "System database driver execution fault.";
    }

    // Redirect cleanly back onto the active FAQ workspace page
    header("Location: ../../admin-dashboard.php?page=faqs");
    exit();
}