<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

require_once __DIR__ . '/../models/service/service.php';

$page  = isset($_GET['page'])  ? max(1, (int)$_GET['page'])  : 1;
$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 6;

$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$sort       = $_GET['sort'] ?? 'name_asc';

$data = getServicesLogic($page, $limit, $categoryId, $sort);

echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();
?>