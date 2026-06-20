<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Double-check security execution authority parameters
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Radnik') {
    header("Location: ../usluge.php");
    exit();
}

require_once dirname(__DIR__) . '/config/connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    global $conn;
    try {
        $stmt = $conn->prepare("DELETE FROM services WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        error_log("Greška prilikom brisanja usluge: " . $e->getMessage());
    }
}

// Redirect back cleanly to the updated grid layout view
header("Location: ../usluge.php");
exit();