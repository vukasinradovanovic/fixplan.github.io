<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/connection.php';
// Include your database functions file to leverage existing helper structures
require_once dirname(__DIR__, 2) . '/models/functions/auth.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$message = "";
$statusClass = "alert-danger";
$autoRedirect = false;

if (!empty($token)) {
    try {
        global $conn;
        
        // Select the full row payload to construct our operational session if valid
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, status, is_locked FROM users WHERE verification_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Safety Check: Avoid logging in locked or disabled users immediately
            if ((int)$user['status'] === 0) {
                $message = "Vaš nalog je deaktiviran. Kontaktirajte administraciju.";
            } elseif ((int)$user['is_locked'] === 1) {
                $message = "Ovaj nalog je privremeno zaključan zbog previše neuspešnih pokušaja.";
            } else {
                // Update activation properties
                $updateStmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id");
                $updateStmt->execute(['id' => $user['id']]);

                // Query their exact security role from the database map
                $userRole = getUserRoleNameFromDB($user['id']);

                // Build the active session array payload (Matches loginUserLogic structure)
                $_SESSION['user_id']    = (int)$user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $userRole;

                $message = "Vaš nalog je uspešno verifikovan! Automatski ste prijavljeni. Preusmeravamo vas...";
                $statusClass = "alert-success";
                $autoRedirect = true;
            }
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