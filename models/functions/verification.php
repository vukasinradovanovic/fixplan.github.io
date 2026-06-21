<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/connection.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$message = "";
$statusClass = "alert-danger";

if (!empty($token)) {
    try {
        global $conn;
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id");
            $updateStmt->execute(['id' => $user['id']]);

            $message = "Vaš nalog je uspešno verifikovan! Sada se možete prijaviti.";
            $statusClass = "alert-success";
        } else {
            $message = "Nevalidan ili zastareo verifikacioni kod.";
        }
    } catch (PDOException $e) {
        error_log("Database verification link fault: " . $e->getMessage());
        $message = "Došlo je do sistemske greške tokom obrade verifikacije.";
    }
} else {
    $message = "Nedostaje token parametar za aktivaciju.";
}