<?php
require_once "auth.php";
requireLoginJson();
require_once "db.php";
header("Content-Type: application/json");

$stmt = $pdo->query("SELECT rolle_id, rolle_name FROM public.rolle ORDER BY rolle_id");
echo json_encode($stmt->fetchAll());
