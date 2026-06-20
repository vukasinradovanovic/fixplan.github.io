<?php
require_once __DIR__ . '/../functions/services.php';
require_once __DIR__ . '/../functions/thumbnailGenerator.php';
require_once __DIR__ . '/../functions/guards.php';

/**
 * Automatski generiše URL-friendly slug iz datog stringa, sa proverom jedinstvenosti u bazi podataka.
 * @param string $string
 * @param int $id Se koristi za izuzimanje trenutnog ID-a prilikom provere jedinstvenosti (za update slučajeve).
 * @return string
 */
function generateBackendSlug($string, $id = 0)
{
    $matrix = [
        'š' => 's',
        'đ' => 'dj',
        'č' => 'c',
        'ć' => 'c',
        'ž' => 'z',
        'Š' => 'S',
        'Đ' => 'Dj',
        'Č' => 'C',
        'Ć' => 'C',
        'Ž' => 'Z'
    ];
    $string = strtr($string, $matrix);

    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');

    $baseSlug = $slug;
    $counter = 1;

    while (isSlugExistsInDB($slug, $id)) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}

/**
 * Dohvata listu usluga sa paginacijom, filtriranjem po kategoriji, sortiranje i pretragu.
 * @param int $page
 * @param int $limit
 * @param int|null $categoryId
 * @param string $sort
 * @param string|null $search
 * @return array $formattedItems
 */
function getServicesLogic($page = 1, $limit = 6, $categoryId = null, $sort = 'name_asc', $search = null)
{
    $page   = max(1, (int)$page);
    $limit  = max(1, (int)$limit);
    $offset = ($page - 1) * $limit;

    $rawServices = getPaginatedServicesFromDB($limit, $offset, $categoryId, $sort, $search);
    $totalItems  = getTotalServicesCount($categoryId, $search);
    $totalPages  = ceil($totalItems / $limit);

    $formattedItems = array_map(function ($service) {
        return [
            'id'       => (int)($service['id'] ?? 0),
            'label'    => htmlspecialchars($service['name'] ?? ''),
            'value'    => htmlspecialchars($service['slug'] ?? ''),
            'desc'     => htmlspecialchars($service['description'] ?? ''),
            'category' => !empty($service['category_name']) ? htmlspecialchars($service['category_name']) : 'Nekategorisano',
            'bgi'      => !empty($service['bgi']) ? htmlspecialchars($service['bgi']) : 'default.png'
        ];
    }, $rawServices);

    return [
        'metadata' => [
            'total_items'  => $totalItems,
            'total_pages'  => $totalPages,
            'current_page' => $page,
            'limit'        => $limit
        ],
        'items' => $formattedItems
    ];
}

/**
 * Dohvata detalje o usluzi za prikaz u formi za uređivanje.
 * @param int $id
 * @return array|false
 */
function getServiceFormDetails($id)
{
    return ($id > 0) ? getServiceByIdFromDB($id) : false;
}

/**
 * Obrađuje logiku za unos ili ažuriranje usluge, uključujući validaciju, obradu slike i upis u bazu podataka.
 * @param string $name
 * @param string $description
 * @param array|null $imageFile
 * @param int $categoryId
 * @param int $id
 * @param int|null $createdBy
 * @return array ["success" => bool, "message" => string]
 */
function processServiceSubmissionLogic($name, $description, $imageFile, $categoryId, $id = 0, $createdBy = null)
{
    $name        = trim($name);
    $description = trim($description);
    $categoryId  = (int)$categoryId;
    $id          = (int)$id;

    if (empty($name) || $categoryId <= 0) {
        return ["success" => false, "message" => "Sva polja uključujući i kategoriju su obavezna."];
    }

    // Process automated slug transformation seamlessly
    $slug = generateBackendSlug($name, $id);

    $finalImageName = "";
    $oldImageName   = "";

    if ($id > 0) {
        $existingService = getServiceByIdFromDB($id);
        if ($existingService) {
            $oldImageName = $existingService['bgi'] ?? '';
        }
    }

    if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $imageFile['tmp_name'];
        $fileName      = $imageFile['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            return ["success" => false, "message" => "Pogrešna ekstenzija fajla. Dozvoljeni formati: JPG, JPEG, PNG, WEBP."];
        }

        $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
        $baseDir     = dirname(__DIR__, 2) . '/public/img/';
        $origDir     = $baseDir . 'original/';
        $thumbDir    = $baseDir . 'thumbnails/';

        if (!is_dir($origDir))  mkdir($origDir, 0755, true);
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

        if (move_uploaded_file($fileTmpPath, $origDir . $newFileName)) {
            if (generateThumbnail($origDir . $newFileName, $thumbDir . $newFileName, $fileExtension, 400)) {
                $finalImageName = $newFileName;
                if (!empty($oldImageName) && $oldImageName !== 'default.png') {
                    if (file_exists($origDir . $oldImageName))  @unlink($origDir . $oldImageName);
                    if (file_exists($thumbDir . $oldImageName)) @unlink($thumbDir . $oldImageName);
                }
            } else {
                @unlink($origDir . $newFileName);
                return ["success" => false, "message" => "Greška prilikom generisanja sličice (thumbnail)."];
            }
        } else {
            return ["success" => false, "message" => "Greška pri čuvanju originalne slike na serveru."];
        }
    }

    $isSaved = saveServiceToDB($name, $slug, $description, $finalImageName, $categoryId, $id, $createdBy);

    return $isSaved
        ? ["success" => true, "message" => "Usluga uspešno sačuvana."]
        : ["success" => false, "message" => "Greška prilikom upisa u bazu podataka."];
}


/** 
 * Obrada POST zahteva za unos ili ažuriranje usluge
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    protectRoute(['Radnik'], '../../index.php');

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $currentUserId = $_SESSION['user_id'] ?? null;

    $result = processServiceSubmissionLogic(
        $_POST['name'] ?? '',
        $_POST['description'] ?? '',
        $_FILES['image'] ?? null,
        $_POST['category_id'] ?? 0,
        $id,
        $currentUserId
    );

    if ($result['success']) {
        header("Location: ../../usluge.php");
    } else {
        $_SESSION['form_error_message'] = $result['message'];
        header("Location: ../../manage_service.php" . ($id > 0 ? "?id={$id}" : ""));
    }
    exit();
}
