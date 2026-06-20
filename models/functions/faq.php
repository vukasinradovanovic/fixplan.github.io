<?php
require_once __DIR__ . '/../../config/connection.php';

/**
 * Fetch all active FAQ entries from the database
 * @return array Array of data objects
 */
function getAllFAQsFromDB() {
    global $conn;
    try {
        // Double-check that your database table name and column fields match these exactly
        $query = "SELECT id, question, answer FROM faqs ORDER BY id ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error in getAllFAQsFromDB: " . $e->getMessage());
        return [];
    }
}