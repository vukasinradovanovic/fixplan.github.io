<?php
require_once __DIR__ . '/../../config/connection.php';

/**
 * Fetch all available categories for dropdown components
 * @return array
 */
function getAllCategoriesFromDB() {
    global $conn;
    try {
        $query = "SELECT id, name, slug FROM categories ORDER BY name ASC";
        return $conn->query($query)->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error in getAllCategoriesFromDB: " . $e->getMessage());
        return [];
    }
}

/**
 * Fetch a paginated chunk of services mapped alongside their parent category name
 */
function getPaginatedServicesFromDB($limit, $offset) {
    global $conn;
    try {
        $query = "SELECT s.id, s.name, s.slug, s.description, s.bgi, c.name AS category_name, s.category_id 
                  FROM services s 
                  LEFT JOIN categories c ON s.category_id = c.id 
                  ORDER BY s.id ASC LIMIT :limit OFFSET :offset";
        $stmt = $conn->prepare($query);
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database error in getPaginatedServicesFromDB: " . $e->getMessage());
        return [];
    }
}

/**
 * Get total count of services
 * @return int
 */
function getTotalServicesCount() {
    global $conn;
    try {
        $query = "SELECT COUNT(*) FROM services";
        return (int)$conn->query($query)->fetchColumn();
    } catch (PDOException $e) {
        error_log("Database error in getTotalServicesCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Fetch a single service alongside its category assignments
 */
function getServiceByIdFromDB($id) {
    global $conn;
    try {
        $query = "SELECT id, category_id, name, slug, description, bgi FROM services WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Database error in getServiceByIdFromDB: " . $e->getMessage());
        return false;
    }
}

/**
 * Save a service record with dynamic category mappings and creator logging
 */
function saveServiceToDB($name, $slug, $description, $bgi, $categoryId, $id = 0, $createdBy = null) {
    global $conn;
    try {
        $categoryId = $categoryId > 0 ? (int)$categoryId : null;

        if ($id > 0) {
            // Edit mode: Keep the original creator intact
            $query = "UPDATE services 
                      SET category_id = :category_id, name = :name, slug = :slug, description = :description, bgi = :bgi 
                      WHERE id = :id";
            $params = [
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'bgi'         => $bgi,
                'id'          => (int)$id
            ];
        } else {
            // Insertion mode: Record the user adding the service
            $query = "INSERT INTO services (category_id, name, slug, description, bgi, created_by) 
                      VALUES (:category_id, :name, :slug, :description, :bgi, :created_by)";
            $params = [
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'bgi'         => $bgi,
                'created_by'  => $createdBy ? (int)$createdBy : null
            ];
        }

        $stmt = $conn->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Database error in saveServiceToDB: " . $e->getMessage());
        return false;
    }
}