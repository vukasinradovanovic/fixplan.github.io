<?php
session_start();
require_once __DIR__ . '/models/auth.php';

logoutUserLogic();

// Bounce the browser tracking back to the homepage
header("Location: index.php");
exit();
?>