<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function requireLoginJson() {
  if (!isset($_SESSION["user"])) {
    http_response_code(401);
    header("Content-Type: application/json");
    echo json_encode(["error" => "Nicht eingeloggt"]);
    exit;
  }
}

function logoutUser() {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
  }
  session_destroy();
}

function isAdminSession() {
  $u = $_SESSION["user"] ?? null;
  if (!$u) return false;
  $roleName = $u["rolle_name"] ?? "";
  $roleId = (int)($u["rolle_id"] ?? 0);
  return ($roleName === "Admin" || $roleId === 1);
}
