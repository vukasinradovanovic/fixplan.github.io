<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Guard: Simple check. Adjust session properties if you enforce Role ID lookups.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php"); 
    exit();
}

require_once dirname(__DIR__, 3) . '/models/admin_stats.php';
$logData = getPageStatsFromLog();

$adminName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$adminEmail = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixPlan | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    