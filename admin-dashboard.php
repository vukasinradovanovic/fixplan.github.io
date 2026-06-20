<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/views/dashboard/layout/head-dashboard.php';

include_once __DIR__ . '/views/dashboard/admin-dashboard.php';

include_once __DIR__ . '/views/dashboard/layout/script-dashboard.php';