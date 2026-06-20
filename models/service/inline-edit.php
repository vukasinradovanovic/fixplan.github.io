<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load ONLY the raw DB helper utility layers directly to prevent accidental procedural code execution
require_once __DIR__ . '/../functions/services.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../../index.php");
    exit();
}

/**
 * Isolated helper to generate clean SEO URLs on inline updates
 */
function generateInlineSlugHelper($title, $id = 0) {
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
    $slug = preg_replace('/[^a-zA-Z0-9\/_|+ -]/', '', $slug);
    $slug = strtolower(trim($slug, '-'));
    $slug = preg_replace('/[\/_|+ -]+/', '-', $slug);

    $baseSlug = $slug;
    $counter = 1;
    while (isSlugExistsInDB($slug, $id)) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    return $slug;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        $_SESSION['form_error_message'] = "Nevalidan ID oznake usluge.";
        header("Location: ../../admin-dashboard.php?page=services");
        exit();
    }

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);

    if (empty($name) || $categoryId <= 0) {
        $_SESSION['form_error_message'] = "Sva polja obeležena zvezdicom su obavezna.";
        header("Location: ../../admin-dashboard.php?page=services");
        exit();
    }

    // 1. Generate clean URL reference string safely
    $slug = generateInlineSlugHelper($name, $id);

    // 2. Fetch existing service configuration to prevent erasing structural image parameters
    $existingService = getServiceByIdFromDB($id);
    $currentImageFilename = $existingService ? ($existingService['bgi'] ?? '') : '';

    $finalImageName = "";

    // 3. Process structural file streams safely
    $imageFile = $_FILES['image'] ?? null;
    if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $imageFile['tmp_name'];
        $fileName      = $imageFile['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
            $baseDir     = dirname(__DIR__, 2) . '/public/img/';
            $origDir     = $baseDir . 'original/';
            $thumbDir    = $baseDir . 'thumbnails/';

            if (!is_dir($origDir))  mkdir($origDir, 0755, true);
            if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

            if (move_uploaded_file($fileTmpPath, $origDir . $newFileName)) {
                // Check if your environment handles thumbnail processing
                if (function_exists('generateThumbnail')) {
                    generateThumbnail($origDir . $newFileName, $thumbDir . $newFileName, $fileExtension, 400);
                } else {
                    @copy($origDir . $newFileName, $thumbDir . $newFileName);
                }
                
                $finalImageName = $newFileName;
                
                // Clear old custom system assets safely
                if (!empty($currentImageFilename) && $currentImageFilename !== 'default.png') {
                    if (file_exists($origDir . $currentImageFilename))  @unlink($origDir . $currentImageFilename);
                    if (file_exists($thumbDir . $currentImageFilename)) @unlink($thumbDir . $currentImageFilename);
                }
            }
        }
    }

    // FIX: If no new image was uploaded, retain the current filename to preserve the relation in saveServiceToDB
    if (empty($finalImageName)) {
        $finalImageName = $currentImageFilename;
    }

    // 4. Update the DB record
    $isSaved = saveServiceToDB($name, $slug, $description, $finalImageName, $categoryId, $id, $_SESSION['user_id']);

    if ($isSaved) {
        $_SESSION['form_success_message'] = "Usluga '<strong>" . htmlspecialchars($name) . "</strong>' je uspešno ažurirana.";
    } else {
        $_SESSION['form_error_message'] = "Sistemska greška prilikom upisa izmena u bazu podataka.";
    }

    // 5. Redirect straight back to your active dashboard module view pane
    header("Location: ../../admin-dashboard.php?page=services");
    exit();
}