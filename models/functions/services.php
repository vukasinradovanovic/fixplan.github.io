<?php
require_once __DIR__ . '/../../config/connection.php';

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

function getPaginatedServicesFromDB($limit, $offset) {
    global $conn;
    try {
        $query = "SELECT s.id, s.name, s.slug, s.description, img.filename AS bgi, c.name AS category_name, s.category_id, s.id_image 
                  FROM services s 
                  LEFT JOIN categories c ON s.category_id = c.id 
                  LEFT JOIN service_images img ON s.id_image = img.id_image
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

function getServiceByIdFromDB($id) {
    global $conn;
    try {
        $query = "SELECT s.id, s.category_id, s.name, s.slug, s.description, s.id_image, img.filename AS bgi 
                  FROM services s
                  LEFT JOIN service_images img ON s.id_image = img.id_image
                  WHERE s.id = :id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Database error in getServiceByIdFromDB: " . $e->getMessage());
        return false;
    }
}

/**
 * Persists image details and handles transactional relationships
 */
function saveServiceToDB($name, $slug, $description, $filename, $categoryId, $id = 0, $createdBy = null) {
    global $conn;
    try {
        $conn->beginTransaction();
        $categoryId = $categoryId > 0 ? (int)$categoryId : null;
        $imageId = null;

        // Step A: Handle structural assignments if a file token was processed
        if (!empty($filename)) {
            if ($id > 0) {
                // If editing, check if an asset record exists
                $existing = getServiceByIdFromDB($id);
                $oldImageId = $existing ? ($existing['id_image'] ?? null) : null;
                
                if ($oldImageId) {
                    $imgQuery = "UPDATE service_images SET filename = :filename WHERE id_image = :id_image";
                    $conn->prepare($imgQuery)->execute(['filename' => $filename, 'id_image' => $oldImageId]);
                    $imageId = $oldImageId;
                }
            }

            if (!$imageId) {
                $imgQuery = "INSERT INTO service_images (filename) VALUES (:filename)";
                $stmt = $conn->prepare($imgQuery);
                $stmt->execute(['filename' => $filename]);
                $imageId = (int)$conn->lastInsertId();
            }
        } else if ($id > 0) {
            // Keep existing image reference if no new file is provided during edit
            $existing = getServiceByIdFromDB($id);
            $imageId = $existing ? ($existing['id_image'] ?? null) : null;
        }

        // Step B: Persist service record alterations
        if ($id > 0) {
            $query = "UPDATE services 
                      SET category_id = :category_id, id_image = :id_image, name = :name, slug = :slug, description = :description 
                      WHERE id = :id";
            $params = [
                'category_id' => $categoryId,
                'id_image'    => $imageId,
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'id'          => (int)$id
            ];
        } else {
            $query = "INSERT INTO services (category_id, id_image, name, slug, description, created_by) 
                      VALUES (:category_id, :id_image, :name, :slug, :description, :created_by)";
            $params = [
                'category_id' => $categoryId,
                'id_image'    => $imageId,
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'created_by'  => $createdBy ? (int)$createdBy : null
            ];
        }

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Database error in saveServiceToDB: " . $e->getMessage());
        return false;
    }
}