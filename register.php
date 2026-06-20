<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/models/auth.php';

$message = "";
$messageClass = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnRegister'])) {
    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = registerUserLogic($firstName, $lastName, $email, $password);
    
    if ($result['success']) {
        header("Location: index.php");
        exit();
    } else {
        $message = $result['message'];
        $messageClass = 'alert-danger';
    }
}

$pageTitle = "Registracija"; 

include_once 'views/components/fixed/head.php';
include_once 'views/components/loader.php';
include_once 'views/components/fixed/header.php';

// Inject page content
include_once 'views/pages/register.php';

include_once 'views/components/fixed/footer.php';
include_once 'views/components/fixed/scripts.php';
?>