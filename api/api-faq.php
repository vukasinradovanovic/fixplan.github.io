<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Method: GET");

require_once __DIR__ . '/../models/faq.php';

// Fetch processed database properties
$data = getFAQLogic();

// Return clean JSON objects
echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();
?>