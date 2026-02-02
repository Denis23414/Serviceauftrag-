<?php
require_once "auth.php";
requireLoginJson();
require_once "db.php";
header("Content-Type: application/json");

function bad($m,$c=400){ http_response_code($c); echo json_encode(["error"=>$m]); exit; }
function ok($p){ echo json_encode($p); exit; }

if (!isAdminSession()) bad("Keine Berechtigung (nur Admin).", 403);
if ($_SERVER["REQUEST_METHOD"] !== "POST") bad("Method not allowed", 405);

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) bad("Keine Daten empfangen.");

$username = trim($data["username"] ?? "");
$password = $data["password"] ?? "";
$vorname  = trim($data["vorname"] ?? "");
$nachname = trim($data["nachname"] ?? "");
$rolle_id = (int)($data["rolle_id"] ?? 0);

if ($username==="" || $password==="" || $vorname==="" || $nachname==="" || $rolle_id<=0) bad("Pflichtfelder fehlen.");
if (strlen($password) < 6) bad("Passwort zu kurz (min. 6).");

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
  $stmt = $pdo->prepare("
    INSERT INTO public.benutzer (username, passwort_hash, vorname, nachname, rolle_id)
    VALUES (:u,:h,:v,:n,:rid)
    RETURNING benutzer_id
  ");
  $stmt->execute([
    ":u"=>$username, ":h"=>$hash, ":v"=>$vorname, ":n"=>$nachname, ":rid"=>$rolle_id
  ]);
  ok(["status"=>"success", "benutzer_id"=>$stmt->fetchColumn()]);
} catch (PDOException $e) {
  if (str_contains($e->getMessage(),"unique") || str_contains($e->getMessage(),"duplicate")) {
    bad("Username existiert bereits.", 409);
  }
  bad("DB Fehler: ".$e->getMessage(), 500);
}
