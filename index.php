<?php
session_start();
// Set layout settings dynamically
$pageTitle = "Početna"; 

// Compile the webpage using components and views
include_once 'views/components/fixed/head.php';
include_once 'views/components/loader.php';
include_once 'views/components/fixed/header.php';

// Inject page content
include_once 'views/pages/home.php';

include_once 'views/components/fixed/footer.php';
include_once 'views/components/fixed/scripts.php';
?>