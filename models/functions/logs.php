<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/connection.php';
require_once __DIR__ . '/auth.php';


/**
 * Prikuplja trenutni kontekst pristupa korisnika za potrebe logovanja.
 * @return array Asocijativni niz sa detaljima o korisniku, roli, stranici i IP adresi
 */
function captureCurrentAccessContext() {
    $userIdentifier = "Gost";
    $userRole = "Gost";

    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $userIdentifier = "Korisnik ID: " . $userId . " (" . $_SESSION['email'] . ")";
        
        $userRole = getUserRoleNameFromDB($userId);
    }

    $currentPage = basename($_SERVER['SCRIPT_NAME']);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';

    return [
        'timestamp' => date('Y-m-d H:i:s'),
        'user'      => $userIdentifier,
        'role'      => $userRole,
        'page'      => $currentPage,
        'ip'        => $ipAddress
    ];
}