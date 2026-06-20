<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Read the role from the session, fallback to 'Gost' if not logged in
$role = $_SESSION['role'] ?? 'Gost';

echo json_encode([
    'success' => true,
    'role' => trim($role)
]);
exit();