<?php
require_once dirname(__DIR__) . '/models/functions/auth.php';

/**
 * Process User Registration Logic
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
 * Process User Authentication Sign In
 */
function loginUserLogic($email, $password) {
    // Clean up basic whitespace first
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

    // Verify hash signatures match securely
    if (password_verify($password, $user->password)) {
        
        // Fetch the user's role name from the database dynamically
        // Note: Make sure functions/logs.php is included where this is called, 
        // or copy the getUserRoleNameFromDB helper function into your auth file!
        $userRole = getUserRoleNameFromDB($user->id);

        // Instantiate session variables
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
 * Clear Sessions state details (Logout action)
 */
function logoutUserLogic() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = array();
    session_destroy();
    return true;
}