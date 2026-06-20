<?php
require_once __DIR__ . '/../../config/connection.php';

/**
 * Dobija sve FAQ zapise iz baze podataka.
 * @return array Niz objekata FAQ zapisa
 */
function getAllFAQsFromDB() {
    global $conn;
    try {
        $query = "SELECT id, question, answer FROM faqs ORDER BY id ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error in getAllFAQsFromDB: " . $e->getMessage());
        return [];
    }
}