<?php
session_start();
require_once __DIR__ . '/models/functions/verification.php';


// Set layout settings dynamically
$pageTitle = "Verifikacija"; 

// Compile the webpage using components and views
include_once 'views/components/fixed/head.php';
include_once 'views/components/loader.php';
include_once 'views/components/fixed/header.php';

// Inject page content
require_once __DIR__ . '/views/pages/verification.php';

include_once 'views/components/fixed/footer.php';
include_once 'views/components/fixed/scripts.php';