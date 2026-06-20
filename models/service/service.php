<?php
require_once __DIR__ . '/../functions/services.php';

function getServicesLogic($page = 1, $limit = 6)
{
    $page = max(1, (int)$page);
    $limit = max(1, (int)$limit);
    $offset = ($page - 1) * $limit;

    $rawServices = getPaginatedServicesFromDB($limit, $offset);
    $totalItems  = getTotalServicesCount();
    $totalPages  = ceil($totalItems / $limit);

    $formattedItems = array_map(function ($service) {
        $isArr  = is_array($service);
        $id     = $isArr ? ($service['id'] ?? 0) : ($service->id ?? 0);
        $name   = $isArr ? ($service['name'] ?? '') : ($service->name ?? '');
        $slug   = $isArr ? ($service['slug'] ?? '') : ($service->slug ?? '');
        $desc   = $isArr ? ($service['description'] ?? '') : ($service->description ?? '');
        $cat    = $isArr ? ($service['category_name'] ?? '') : ($service->category_name ?? '');
        $bgi    = $isArr ? ($service['bgi'] ?? '') : ($service->bgi ?? '');

        return [
            'id'       => (int)$id,
            'label'    => htmlspecialchars($name),
            'value'    => htmlspecialchars($slug),
            'desc'     => htmlspecialchars($desc),
            'category' => !empty($cat) ? htmlspecialchars($cat) : 'Nekategorisano',
            'bgi'      => !empty($bgi) ? htmlspecialchars($bgi) : 'default.png'
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

function getServiceFormDetails($id)
{
    return ($id > 0) ? getServiceByIdFromDB($id) : false;
}

function processServiceSubmissionLogic($name, $slug, $description, $imageFile, $categoryId, $id = 0, $createdBy = null)
{
    $name        = trim($name);
    $slug        = trim($slug);
    $description = trim($description);
    $categoryId  = (int)$categoryId;
    $id          = (int)$id;

    if (empty($name) || empty($slug) || $categoryId <= 0) {
        return ["success" => false, "message" => "Sva polja uključujući i kategoriju su obavezna."];
    }

    $finalImageName = "";
    $oldImageName   = "";

    if ($id > 0) {
        $existingService = getServiceByIdFromDB($id);
        if ($existingService) {
            $isArr          = is_array($existingService);
            $oldImageName   = $isArr ? ($existingService['bgi'] ?? '') : ($existingService->bgi ?? '');
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

function generateThumbnail($sourcePath, $destPath, $extension, $targetWidth = 400) {
    list($origWidth, $origHeight) = getimagesize($sourcePath);
    if (!$origWidth || !$origHeight) return false;

    $targetHeight = round($targetWidth * ($origHeight / $origWidth));

    switch ($extension) {
        case 'jpeg':
        case 'jpg':  $srcImage = imagecreatefromjpeg($sourcePath); break;
        case 'png':  $srcImage = imagecreatefrompng($sourcePath);  break;
        case 'webp': $srcImage = imagecreatefromwebp($sourcePath); break;
        default:     return false;
    }

    if (!$srcImage) return false;

    $thumbImage = imagecreatetruecolor($targetWidth, $targetHeight);

    if ($extension === 'png' || $extension === 'webp') {
        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);
    }

    imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $origWidth, $origHeight);

    switch ($extension) {
        case 'jpeg':
        case 'jpg':  $result = imagejpeg($thumbImage, $destPath, 85); break;
        case 'png':  $result = imagepng($thumbImage, $destPath, 6);   break;
        case 'webp': $result = imagewebp($thumbImage, $destPath, 80); break;
        default:     $result = false;
    }

    imagedestroy($srcImage);
    imagedestroy($thumbImage);
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Radnik') {
        header("Location: ../../usluge.php");
        exit();
    }

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $currentUserId = $_SESSION['user_id'] ?? null;

    $result = processServiceSubmissionLogic(
        $_POST['name'] ?? '',
        $_POST['slug'] ?? '',
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