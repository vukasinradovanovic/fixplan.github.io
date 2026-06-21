<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Authorization protection guard
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Neautorizovan pristup."]);
    exit;
}

require_once dirname(__DIR__) . '/models/functions/auth.php';

// Get JSON raw payload inputs
$input = json_decode(file_get_contents('php://input'), true);

$firstName = trim($input['firstName'] ?? '');
$lastName  = trim($input['lastName'] ?? '');
$email     = trim($input['email'] ?? '');
$userId    = $_SESSION['user_id'];

// Server-side validation check
$allowedDomainsPattern = '/^[^@]+@(gmail|example|fixplan|hotmail)\.[a-zA-Z]{2,6}$/i';

if (empty($firstName) || empty($lastName) || !preg_match($allowedDomainsPattern, $email)) {
    echo json_encode(["success" => false, "message" => "Sva polja su obavezna i email mora biti validan."]);
    exit;
}

// Perform DB execution update mapping rules
if (updateUserInfo($userId, $firstName, $lastName, $email)) {
    // Sync active session context state records dynamically
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name']  = $lastName;
    $_SESSION['email']      = $email;

    echo json_encode(["success" => true, "message" => "Podaci su uspešno ažurirani!"]);
} else {
    echo json_encode(["success" => false, "message" => "Došlo je do sistemske greške pri čuvanju podataka."]);
}