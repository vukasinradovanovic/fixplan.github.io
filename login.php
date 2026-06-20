<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'models/auth.php';

$errorMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnLogin'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = loginUserLogic($email, $password);
    
    if ($result['success']) {
        header("Location: index.php");
        exit();
    } else {
        $errorMessage = $result['message'];
    }
}

$pageTitle = "Prijava"; 

// Compile the webpage using components and views
include_once 'views/components/fixed/head.php';
include_once 'views/components/loader.php';
include_once 'views/components/fixed/header.php';

// Inject page content
include_once 'views/pages/login.php';

include_once 'views/components/fixed/footer.php';
include_once 'views/components/fixed/scripts.php';
?>