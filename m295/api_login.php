<?php
require_once "auth.php";
require_once "db.php";
header("Content-Type: application/json");

function bad($m,$c=400){ http_response_code($c); echo json_encode(["error"=>$m]); exit; }
function ok($p){ echo json_encode($p); exit; }

if (isset($_GET["logout"])) {
  logoutUser();
  ok(["status"=>"success"]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") bad("Method not allowed", 405);

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) bad("Keine Daten empfangen.");

$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";

if ($username === "" || $password === "") bad("Bitte Username und Passwort eingeben.");

$stmt = $pdo->prepare("
  SELECT b.benutzer_id, b.username, b.vorname, b.nachname, b.passwort_hash, b.rolle_id,
         r.rollen_name AS rolle_name
  FROM public.benutzer b
  LEFT JOIN public.rolle r ON b.rolle_id = r.rolle_id
  WHERE b.username = :u
  LIMIT 1
");

$stmt->execute([":u"=>$username]);
$row = $stmt->fetch();

if (!$row || !$row["passwort_hash"] || !password_verify($password, $row["passwort_hash"])) {
  bad("Login falsch.", 401);
}

$_SESSION["user"] = [
  "benutzer_id" => $row["benutzer_id"],
  "username" => $row["username"],
  "vorname" => $row["vorname"],
  "nachname" => $row["nachname"],
  "rolle_id" => $row["rolle_id"],
  "rolle_name" => $row["rolle_name"]
];

ok(["status"=>"success"]);
