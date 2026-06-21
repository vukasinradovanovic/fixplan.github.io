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