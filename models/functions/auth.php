<?php
require_once __DIR__ . '/../../config/connection.php';

/**
 * Dobija korisnički zapis iz baze podataka po email adresi.
 * @param string $email
 * @return object|bool Vraća korisnički objekat ili false ako nije pronađen.
 */
function getUserByEmail($email) {
    global $conn;
    try {
        $query = "SELECT * FROM users WHERE email = :email AND status = 1 LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Database error in getUserByEmail: " . $e->getMessage());
        return false;
    }
}

/**
 * Ubacuje novog korisnika u bazu podataka sa hashiranom lozinkom i verifikacionim tokenom.
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @param string $passwordHashed
 * @param string $verificationToken
 * @return int|bool Vraća ID novog korisnika ili false ako nije uspešno.
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
        $stmt->bindParam(':verification_token', $verificationToken, PDO::PARAM_STR); // FIXED: Bound verification token parameter
        
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
 * Dodaje korisniku profilnu vezu za sigurnosnu ulogu
 * @param int $userId
 * @param int $roleId podrazumevani 3 (Klijent)
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
 * Dobija ime uloge korisnika iz baze podataka na osnovu ID-a korisnika.s
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
 * Ažurira osnovne podatke o korisniku u bazi podataka.
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
 * Ažurira osnovne podatke o korisniku inline unutar transakcije sa proverom duplata email-a.
 * @param int $id
 * @param string $firstName
 * @param string $lastName
 * @param string $email
 * @param int $isVerified
 * @param int $isLocked
 * @return array Vraća niz sa statusom 'success' i pratećom porukom.
 */
function updateUserInlineInDB($id, $firstName, $lastName, $email, $isVerified, $isLocked) {
    global $conn;
    try {
        $conn->beginTransaction();

        $id = (int)$id;
        $isVerified = (int)$isVerified;
        $isLocked = (int)$isLocked;

        // Provera da li email već pripada drugom korisniku kako bi se izbegao konflikt duplata
        $emailCheckStmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
        $emailCheckStmt->execute(['email' => $email, 'id' => $id]);
        
        if ($emailCheckStmt->fetch()) {
            if ($conn->inTransaction()) $conn->rollBack();
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