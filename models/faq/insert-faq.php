<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/connection.php';

// Authorization Rule guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question     = trim($_POST['question'] ?? '');
    $answer       = trim($_POST['answer'] ?? '');
    $displayOrder = $_POST['display_order'] !== '' ? (int)$_POST['display_order'] : null;

    if (empty($question) || empty($answer)) {
        $_SESSION['form_error_message'] = "Please fill out both the question and answer inputs.";
        header("Location: ../../admin-dashboard.php?page=add-faq");
        exit();
    }

    try {
        global $conn;

        // Auto-calculate structural display position rank index if left unassigned
        if ($displayOrder === null) {
            $orderCheck = $conn->query("SELECT MAX(display_order) FROM faqs")->fetchColumn();
            $displayOrder = $orderCheck ? ((int)$orderCheck + 1) : 1;
        }

        $query = "INSERT INTO faqs (question, answer, display_order) VALUES (:question, :answer, :display_order)";
        $stmt = $conn->prepare($query);
        $executed = $stmt->execute([
            'question'      => $question,
            'answer'        => $answer,
            'display_order' => $displayOrder
        ]);

        if ($executed) {
            $_SESSION['form_success_message'] = "New FAQ published into database successfully.";
            // Go back directly to primary FAQ listing page view on victory
            header("Location: ../../admin-dashboard.php?page=faqs");
            exit();
        } else {
            $_SESSION['form_error_message'] = "Application failed to compile record row metadata entry.";
            header("Location: ../../admin-dashboard.php?page=add-faq");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Database error inside insert_faq.php: " . $e->getMessage());
        $_SESSION['form_error_message'] = "Internal database driver structural error handling request.";
        header("Location: ../../admin-dashboard.php?page=add-faq");
        exit();
    }
}