<?php
require_once __DIR__ . '/../../config/connection.php';

/**
 * Dobija sve kategorije iz baze podataka.
 * @return array
 */
function getAllCategoriesFromDB()
{
    global $conn;
    try {
        $query = "SELECT id, name, slug FROM categories ORDER BY name ASC";
        return $conn->query($query)->fetchAll(PDO::FETCH_ASSOC); // Eksplicitno vraća asocijativni niz
    } catch (PDOException $e) {
        error_log("Database error in getAllCategoriesFromDB: " . $e->getMessage());
        return [];
    }
}

/**
 * Dobija paginirane usluge iz baze podataka sa opcionalnim filtriranjem po kategoriji i sortiranjem.
 * @param int $limit Broj usluga za prikaz
 * @param int $offset Pomeraj za paginaciju
 * @param int|null $categoryId ID kategorije za filtriranje
 * @param string $sort Redosled sortiranja
 * @return array Niz objekata usluga
 */
function getPaginatedServicesFromDB($limit, $offset, $categoryId = null, $sort = 'name_asc')
{
    global $conn;
    try {
        $query = "SELECT s.id, s.name, s.slug, s.description, img.filename AS bgi, c.name AS category_name, s.category_id, s.id_image 
                  FROM services s 
                  LEFT JOIN categories c ON s.category_id = c.id 
                  LEFT JOIN service_images img ON s.id_image = img.id_image";

        if ($categoryId !== null) {
            $query .= " WHERE s.category_id = :category_id";
        }

        switch ($sort) {
            case 'name_desc':
                $query .= " ORDER BY s.name DESC";
                break;
            case 'date_desc':
                $query .= " ORDER BY s.id DESC";
                break;
            case 'date_asc':
                $query .= " ORDER BY s.id ASC";
                break;
            case 'name_asc':
            default:
                $query .= " ORDER BY s.name ASC";
                break;
        }

        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($query);

        if ($categoryId !== null) {
            $stmt->bindValue(':category_id', (int)$categoryId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getPaginatedServicesFromDB: " . $e->getMessage());
        return [];
    }
}

/**
 * Dohvata ukupan broj usluga u bazi podataka, sa opcionalnim filtriranjem po kategoriji.
 * @param int|null $categoryId
 * @return int 
 */
function getTotalServicesCount($categoryId = null)
{
    global $conn;
    try {
        $query = "SELECT COUNT(*) FROM services";

        if ($categoryId !== null) {
            $query .= " WHERE category_id = :category_id";
        }

        $stmt = $conn->prepare($query);
        if ($categoryId !== null) {
            $stmt->execute(['category_id' => (int)$categoryId]);
        } else {
            $stmt->execute();
        }

        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Database error in getTotalServicesCount: " . $e->getMessage());
        return 0;
    }
}

/**
 * Dohvata detalje o usluzi iz baze podataka po ID-u.
 * @param int $id
 * @return array|false Vraća asocijativni niz sa detaljima usluge ili false ako nije pronađena.
 */
function getServiceByIdFromDB($id)
{
    global $conn;
    try {
        $query = "SELECT s.id, s.category_id, s.name, s.slug, s.description, s.id_image, img.filename AS bgi 
                  FROM services s
                  LEFT JOIN service_images img ON s.id_image = img.id_image
                  WHERE s.id = :id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->execute(['id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getServiceByIdFromDB: " . $e->getMessage());
        return false;
    }
}

/**
 * Generiše jedinstveni slug za uslugu na osnovu njenog imena, sa proverom u bazi podataka.
 * @param string $name
 * @param string $slug
 * @param string $description
 * @param string $filename
 * @param int $categoryId
 * @param int $id
 * @param int|null $createdBy
 * @return bool Vraća true ako je uspešno sačuvano, false u suprotnom.
 */
function saveServiceToDB($name, $slug, $description, $filename, $categoryId, $id = 0, $createdBy = null)
{
    global $conn;
    try {
        $conn->beginTransaction();
        $categoryId = $categoryId > 0 ? (int)$categoryId : null;
        $id = (int)$id;
        $imageId = null;

        if ($id > 0) {
            $existing = getServiceByIdFromDB($id);
            $imageId = $existing ? ($existing['id_image'] ?? null) : null;
        }

        if (!empty($filename) && ($existing ? $existing['bgi'] !== $filename : true)) {
            $imgQuery = "INSERT INTO service_images (filename) VALUES (:filename)";
            $imgStmt = $conn->prepare($imgQuery);
            $imgStmt->execute(['filename' => $filename]);
            $imageId = (int)$conn->lastInsertId();
        }

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
                'id'          => $id
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

/**
 * Proverava da li slug već postoji u bazi podataka, isključujući trenutni ID (za ažuriranje).
 * @param string $slug
 * @param int $excludeId
 * @return bool
 */
function isSlugExistsInDB($slug, $excludeId = 0)
{
    global $conn;
    try {
        $query = "SELECT COUNT(*) FROM services WHERE slug = :slug AND id != :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            'slug' => $slug,
            'id'   => (int)$excludeId
        ]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Database error in isSlugExistsInDB: " . $e->getMessage());
        return true;
    }
}
