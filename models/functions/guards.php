<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Štiti rutu tako što proverava da li je korisnik prijavljen i da li ima odgovarajuću ulogu.
 * Izvršava preusmeravanje na zadatu lokaciju ako provere ne uspeju.
 * * @param array $allowedRoles Niz dozvoljenih uloga za pristup ruti.
 * @param string $redirectPath Putanja na koju će korisnik biti preusmeren ako nema pristup.
 * @return void
 */
function protectRoute(array $allowedRoles, string $redirectPath = '../../index.php'): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $redirectPath);
        exit();
    }

    $userRole = $_SESSION['role'] ?? '';
    if (!in_array($userRole, $allowedRoles, true)) {
        header("Location: " . $redirectPath);
        exit();
    }
}