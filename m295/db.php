<?php
$host = "aws-1-eu-west-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.zvnpsbnouducksrgddbc";
$password = "DSHADHSJUH31";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
  $pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  header("Content-Type: application/json");
  echo json_encode(["error" => "Verbindungsfehler: " . $e->getMessage()]);
  exit;
}
