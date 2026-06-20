<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/models/functions/guards.php';

protectRoute(['Radnik'], '/index.php');

// Include your functions and models layer
require_once __DIR__ . '/models/functions/services.php';
require_once __DIR__ . '/models/service/service.php';

// Fetch the category list so the view dropdown can render them
$allCategories = getAllCategoriesFromDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$name = $slug = $description = $bgi = "";
$currentCategoryId = 0; 
$isEdit = false;
$message = "";

// Fetch data from database if editing an existing service
if ($id > 0) {
    $service = getServiceFormDetails($id);
    if ($service) {
        $isArr             = is_array($service);
        $name              = $isArr ? ($service['name'] ?? '') : ($service->name ?? '');
        $slug              = $isArr ? ($service['slug'] ?? '') : ($service->slug ?? '');
        $description       = $isArr ? ($service['description'] ?? '') : ($service->description ?? '');
        $bgi               = $isArr ? ($service['bgi'] ?? '') : ($service->bgi ?? '');
        $currentCategoryId = $isArr ? ($service['category_id'] ?? 0) : ($service->category_id ?? 0);
        $isEdit            = true;
    }
}

// Check if an error message was passed back through the session from the model wrapper
if (!empty($_SESSION['form_error_message'])) {
    $message = $_SESSION['form_error_message'];
    unset($_SESSION['form_error_message']);
}

// Set dynamic layout header settings
$pageTitle = $isEdit ? "Edit Service" : "Add New Service"; 

// Compile the webpage using component templates
include_once __DIR__ . '/views/components/fixed/head.php';
include_once __DIR__ . '/views/components/loader.php';
include_once __DIR__ . '/views/components/fixed/header.php';

// Inject page content layout (this view can now read all variables declared above)
include_once __DIR__ . '/views/pages/manage_service.php';

include_once __DIR__ . '/views/components/fixed/footer.php';
include_once __DIR__ . '/views/components/fixed/scripts.php';
?>