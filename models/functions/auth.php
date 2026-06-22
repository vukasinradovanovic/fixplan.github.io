<?php
require_once __DIR__ . '/../../config/connection.php';

/**
 * Fetches a single user record from the database by email address.
 * @param string $email
 * @return object|bool Returns user data object or false if not located.
 */
function getUserByEmail($email) {
    global $conn;
    try {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        error_log("Database error in getUserByEmail: " . $e->getMessage());
        return false;
    }
}

/**
 * Inserts a new user record into the database with hashed security credentials.
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @param string $passwordHashed
 * @param string $verificationToken
 * @return int|bool Returns the newly created user ID or false on operation failure.
 */
function createUser($firstName, $lastName, $email, $passwordHashed, $verificationToken) {
    global $conn;
    try {
        $query = "INSERT INTO users (first_name, last_name, email, password, verification_token, status) 
                  VALUES (:first_name, :last_name, :email, :password, :verification_token, 1)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHashed, PDO::PARAM_STR);
        $stmt->bindParam(':verification_token', $verificationToken, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            return $conn->lastInsertId();
        }
        return false;
    } catch (PDOException $e) {
        error_log("Database error in createUser: " . $e->getMessage());
        return false;
    }
}

/**
 * Maps a specific user account profile to an access role via the junction table.
 * @param int $userId
 * @param int $roleId Default is 3 (Client/Klijent)
 * @return bool
 */
function assignUserRole($userId, $roleId = 3) {
    global $conn;
    try {
        $query = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in assignUserRole: " . $e->getMessage());
        return false;
    }
}

/**
 * Retrieves the specific textual role description mapped to an individual user ID.
 * @param int $userId
 * @return string Role name (e.g., Admin, Klijent)
 */
function getUserRoleNameFromDB($userId) {
    global $conn;
    try {
        $query = "SELECT r.name FROM roles r 
                  INNER JOIN user_roles ur ON r.id = ur.role_id 
                  WHERE ur.user_id = :user_id LIMIT 1";
                  
        $stmt = $conn->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        $role = $stmt->fetchColumn();
        
        return $role ? $role : 'Korisnik';
    } catch (PDOException $e) {
        error_log("Failed to fetch user role for log: " . $e->getMessage());
        return 'Greška pri davanju uloge';
    }
}

/**
 * Updates basic informational context profiles inside the database system layers.
 * @param int $userId
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @return bool
 */
function updateUserInfo($userId, $firstName, $lastName, $email) {
    global $conn;
    try {
        $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in updateUserInfo: " . $e->getMessage());
        return false;
    }
}

/**
 * Logs a malicious or unsuccessful authentication entry inside the tracking registry table.
 * @param string $email
 * @param string $ipAddress
 * @return void
 */
function logFailedAttempt($email, $ipAddress) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip_address)");
        $stmt->execute(['email' => $email, 'ip_address' => $ipAddress]);
    } catch (PDOException $e) {
        error_log("Database error in logFailedAttempt: " . $e->getMessage());
    }
}

/**
 * Evaluates the volume of failed system entry requests inside a strict 5-minute interval windows framework.
 * @param string $email
 * @return int
 */
function countRecentFailures($email): int {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE email = :email 
            AND attempted_at >= NOW() - INTERVAL 5 MINUTE
        ");
        $stmt->execute(['email' => $email]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Database error in countRecentFailures: " . $e->getMessage());
        return 0;
    }
}

/**
 * Updates user profile lock status attributes to prevent access authorization requests.
 * @param string $email
 * @return bool 
 */
function lockUserAccount($email): bool {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE users SET is_locked = 1 WHERE email = :email");
        return $stmt->execute(['email' => $email]);
    } catch (PDOException $e) {
        error_log("Database error in lockUserAccount: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetches all available system global security authorization role matrices.
 * @return array
 */
function getAllRolesFromDB() {
    global $conn;
    try {
        $query = "SELECT id, name FROM roles ORDER BY id ASC";
        return $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAllRolesFromDB: " . $e->getMessage());
        return [];
    }
}

/**
 * Dohvata sve korisnike iz baze podataka, uključujući njihove uloge i status.
 * @return array Objekat sa listom korisnika i njihovim detaljima.
 */
function getAllUsersFromDB() {
    global $conn;
    try {
        $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.is_locked, u.is_verified, u.status, ur.role_id 
                  FROM users u
                  LEFT JOIN user_roles ur ON u.id = ur.user_id 
                  ORDER BY u.id DESC";
        return $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAllUsersFromDB: " . $e->getMessage());
        return [];
    }
}

/**
 * Ažurira korisničke podatke i uloge u bazi podataka, uključujući provere duplikata email adrese.
 * @param int $id
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @param int $isVerified
 * @param int $isLocked
 * @param int $roleId
 * @return array Objekat sa statusom uspeha i porukom o rezultatu operacije.
 */
function updateUserInlineInDB($id, $firstName, $lastName, $email, $isVerified, $isLocked, $roleId) {
    global $conn;
    try {
        $conn->beginTransaction();

        $id = (int)$id;
        $isVerified = (int)$isVerified;
        $isLocked = (int)$isLocked;
        $roleId = (int)$roleId;

        // Provera da li email već pripada drugom korisniku kako bi se izbegao konflikt duplata
        $emailCheckStmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
        $emailCheckStmt->execute(['email' => $email, 'id' => $id]);
        
        if ($emailCheckStmt->fetch()) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            return ["success" => false, "message" => "Korisnik sa email adresom '<strong>" . htmlspecialchars($email) . "</strong>' već postoji u sistemu."];
        }

        // Izvršavanje ažuriranja podataka o korisniku
        $updateQuery = "UPDATE users SET 
                            first_name = :first_name, 
                            last_name = :last_name, 
                            email = :email, 
                            is_verified = :is_verified, 
                            is_locked = :is_locked 
                        WHERE id = :id";
                        
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'email'       => $email,
            'is_verified' => $isVerified,
            'is_locked'   => $isLocked,
            'id'          => $id
        ]);

        // Ažuriranje Korisničkih Uloga (Remove existing constraints, then write updated layout mapping entry)
        $deleteRoleQuery = "DELETE FROM user_roles WHERE user_id = :user_id";
        $deleteStmt = $conn->prepare($deleteRoleQuery);
        $deleteStmt->execute(['user_id' => $id]);

        $insertRoleQuery = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
        $insertStmt = $conn->prepare($insertRoleQuery);
        $insertStmt->execute(['user_id' => $id, 'role_id' => $roleId]);

        $conn->commit();
        return ["success" => true, "message" => "Korisnik '<strong>" . htmlspecialchars($firstName . ' ' . $lastName) . "</strong>' je uspešno ažuriran."];

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Database error in updateUserInlineInDB: " . $e->getMessage());
        return ["success" => false, "message" => "Sistemska greška tokom ažuriranja baze podataka."];
    }
}