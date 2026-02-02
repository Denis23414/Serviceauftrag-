<?php
require_once "auth.php";

$err = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $u = trim($_POST["username"] ?? "");
  $p = trim($_POST["password"] ?? "");

  // TODO: Für Abgabe ok: 1 Fix-User.
  // Besser: später in DB-Tabelle benutzer speichern + password_hash.
  $validUser = "admin";
  $validPass = "admin123";

  if ($u === $validUser && $p === $validPass) {
    loginUser($u);
    header("Location: Index.html");
    exit;
  } else {
    $err = "Login falsch.";
  }
}
?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login - Service Auftrag</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm">
          <div class="card-header fw-bold">Login</div>
          <div class="card-body">
            <?php if ($err): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <form method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input class="form-control" name="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Passwort</label>
                <input type="password" class="form-control" name="password" required>
              </div>
              <button class="btn btn-primary w-100">Einloggen</button>
            </form>

            <div class="text-muted small mt-3">
              Default: admin / admin123
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
