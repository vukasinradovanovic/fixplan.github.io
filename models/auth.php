<?php
require_once dirname(__DIR__) . '/models/functions/auth.php';
require_once dirname(__DIR__) . '/models/functions/mailer.php';

/**
 * Pomoćna funkcija za validaciju email formata i dozvoljenih domena.
 * @param string $email
 * @return bool
 */
function isValidEmailDomain($email) {
    $allowedDomainsPattern = '/^[^@]+@(gmail|example|fixplan|hotmail)\.[a-zA-Z]{2,6}$/i';
    return (bool)preg_match($allowedDomainsPattern, $email);
}

/**
 * Logika za registraciju korisnika sa validacijom i kreiranjem sesije.
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @param string $password
 * @return array 
 */
function registerUserLogic($firstName, $lastName, $email, $password) {
    $firstName = trim($firstName);
    $lastName  = trim($lastName);
    $email     = trim($email);

    if (empty($firstName) || empty($lastName) || !isValidEmailDomain($email) || strlen($password) < 6) {
        return ["success" => false, "message" => "Sva polja su obavezna, email mora biti validan, a lozinka imati bar 6 karaktera."];
    }

    if (getUserByEmail($email)) {
        return ["success" => false, "message" => "Korisnik sa ovim email-om već postoji."];
    }

    $passwordHashed    = password_hash($password, PASSWORD_BCRYPT);
    $verificationToken = bin2hex(random_bytes(32));

    $userId = createUser($firstName, $lastName, $email, $passwordHashed, $verificationToken);
    if ($userId) {
        assignUserRole($userId, 3);

        // Slanje verifikacionog email-a korisniku
        $emailSent = sendVerificationEmail($email, $verificationToken, $firstName);

        if ($emailSent) {
            return ["success" => true, "message" => "Uspešna registracija! Molimo proverite Vaš email kako biste aktivirali nalog pre prve prijave."];
        } else {
            return ["success" => true, "message" => "Nalog kreiran, ali je došlo do greške pri slanju verifikacionog mejla. Obratite se administratoru."];
        }
    }

    return ["success" => false, "message" => "Došlo je do greške prilikom registracije."];
}

/**
 * Logika za prijavu korisnika sa validacijom i kreiranjem sesije.
 * @param string $email
 * @param string $password
 * @return array
 */
function loginUserLogic($email, $password) {
    $email = trim($email);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (empty($email) || empty($password)) {
        return ["success" => false, "message" => "Unesite ispravne podatke za logovanje."];
    }

    if (!isValidEmailDomain($email)) {
        return ["success" => false, "message" => "Email domen nije dozvoljen ili je format neispravan."];
    }

    $user = getUserByEmail($email);
    if (!$user) {
        return ["success" => false, "message" => "Pogrešan email ili lozinka."];
    }

    // Provera da li je nalog uopšte aktivan na sistemu (status kontrola)
    if (isset($user->status) && (int)$user->status === 0) {
        return ["success" => false, "message" => "Vaš nalog je deaktiviran. Kontaktirajte administraciju."];
    }

    // 1. Provera da li je nalog zaključan
    if (isset($user->is_locked) && (int)$user->is_locked === 1) {
        return ["success" => false, "message" => "Ovaj nalog je privremeno zaključan zbog previše neuspešnih pokušaja. Proverite Vaš email."];
    }

    // 2. Provera da li je nalog verifikovan
    if (isset($user->is_verified) && (int)$user->is_verified === 0) {
        return ["success" => false, "message" => "Vaš nalog nije verifikovan. Molimo proverite vašu email adresu za aktivacioni link."];
    }

    // 3. Provera lozinke
    if (password_verify($password, $user->password)) {
        $userRole = getUserRoleNameFromDB($user->id);

        $_SESSION['user_id']    = $user->id;
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name']  = $user->last_name;
        $_SESSION['email']      = $user->email;
        $_SESSION['role']       = $userRole; 
        
        return ["success" => true, "message" => "Dobrodošli nazad!"];
    }

    // Ako lozinka nije tačna, beležimo neuspešan pokušaj
    logFailedAttempt($email, $ipAddress);
    
    $failuresCount = countRecentFailures($email);
    
    if ($failuresCount >= 3) {
        lockUserAccount($email);
        sendAccountLockWarningEmail($email, $user->first_name);
        return ["success" => false, "message" => "Previše neuspešnih pokušaja. Vaš nalog je zaključan, a sigurnosno obaveštenje je poslato na Vaš email."];
    }

    return ["success" => false, "message" => "Pogrešan email ili lozinka."];
}

/**
 * Logika za odjavu korisnika i uništavanje sesije.
 * @return bool
 */
function logoutUserLogic() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = array();
    session_destroy();
    return true;
}

/**
 * Bilježi neuspešan pokušaj prijave u bazu podataka.
 * @param string $email
 * @param string $ipAddress
 * @return void
 */
function logFailedAttempt($email, $ipAddress) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip_address)");
    $stmt->execute(['email' => $email, 'ip_address' => $ipAddress]);
}

/**
 * Broji neuspešne pokušaje za određenog korisnika u poslednjih 5 minuta.
 * @param string $email
 * @return int
 */
function countRecentFailures($email): int {
    global $conn;
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM login_attempts 
        WHERE email = :email 
        AND attempted_at >= NOW() - INTERVAL 5 MINUTE
    ");
    $stmt->execute(['email' => $email]);
    return (int)$stmt->fetchColumn();
}

/**
 * Zaključava korisnički nalog u bazi podataka.
 * @param string $email
 * @return bool 
 */
function lockUserAccount($email): bool {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET is_locked = 1 WHERE email = :email");
    return $stmt->execute(['email' => $email]);
}

/**
 * Dohvata sve registrovane korisnike sortirane od najnovijih ka starijima.
 * @return array Niz asocijativnih nizova sa podacima korisnika ili prazan niz u slučaju greške.
 */
function getAllUsersFromDB() {
    global $conn;
    try {
        $query = "SELECT id, first_name, last_name, email, is_locked, is_verified, status 
                  FROM users 
                  ORDER BY id DESC";
        return $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAllUsersFromDB: " . $e->getMessage());
        return [];
    }
}