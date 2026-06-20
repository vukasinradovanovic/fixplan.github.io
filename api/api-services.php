<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Method: GET");

require_once __DIR__ . '/../models/service/service.php';

// Read values from incoming URL parameters, provide standard default backups
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;

// Gather structural data matrix
$data = getServicesLogic($page, $limit);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();
?>