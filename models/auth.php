<?php
require_once dirname(__DIR__) . '/models/functions/auth.php';

/**
 * Logika za registraciju korisnika a validacijom i kreiranjem sesije.
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @param string $password
 * @return array 
 */
function registerUserLogic($firstName, $lastName, $email, $password) {
    $firstName = trim(htmlspecialchars($firstName));
    $lastName = trim(htmlspecialchars($lastName));
    $email = trim($email);

    $allowedDomainsPattern = '/^[^@]+@(gmail|example|fixplan|hotmail)\.[a-zA-Z]{2,6}$/i';

    if (empty($firstName) || empty($lastName) || !preg_match($allowedDomainsPattern, $email) || strlen($password) < 6) {
        return ["success" => false, "message" => "Sva polja su obavezna, email mora biti validan, a lozinka imati bar 6 karaktera."];
    }

    if (getUserByEmail($email)) {
        return ["success" => false, "message" => "Korisnik sa ovim email-om već postoji."];
    }

    $passwordHashed = password_hash($password, PASSWORD_BCRYPT);

    $userId = createUser($firstName, $lastName, $email, $passwordHashed);
    if ($userId) {
        // Assign Default client role profile (3 = Klijent)
        assignUserRole($userId, 3);

        // Instantiate session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'Klijent'; // <-- Added default role for new registrations

        return ["success" => true, "message" => "Uspešna registracija!"];
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

    if (empty($email) || empty($password)) {
        return ["success" => false, "message" => "Unesite ispravne podatke za logovanje."];
    }

    $allowedDomainsPattern = '/^[^@]+@(gmail|example|fixplan|hotmail)\.[a-zA-Z]{2,6}$/i';

    if (!preg_match($allowedDomainsPattern, $email)) {
        return ["success" => false, "message" => "Email domen nije dozvoljen ili je format neispravan."];
    }

    $user = getUserByEmail($email);
    if (!$user) {
        return ["success" => false, "message" => "Pogrešan email ili lozinka."];
    }

    if (password_verify($password, $user->password)) {
        $userRole = getUserRoleNameFromDB($user->id);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name'] = $user->last_name;
        $_SESSION['email'] = $user->email;
        $_SESSION['role'] = $userRole; 
        
        return ["success" => true, "message" => "Dobrodošli nazad!"];
    }

    return ["success" => false, "message" => "Pogrešan email ili lozinka."];
}

/**
 * Logika za odjavu korisnika i uništavanje sesije.
 * @return bool
 */
function logoutUserLogic() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = array();
    session_destroy();
    return true;
}