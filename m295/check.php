<?php
// check.php
ini_set('display_errors', 1); 
error_reporting(E_ALL);

echo "<h1>Supabase Verbindungs-Test</h1>";

// Verbindungsdaten direkt hier eintragen zum Testen
$host = "aws-1-eu-west-1.pooler.supabase.com"; 
$port = "6543"; // Pooler Port für IPv4
$dbname = "postgres";
$user = "postgres.zvnpsbnouducksrgddbc"; // Benutzername oft mit Projekt-ID bei Pooler
// Alternativ probieren wir den Standard-User, falls oben nicht klappt:
// $user = "postgres"; 

$password = "DSHADHSJUH31"; 

echo "Versuche Verbindung zu: $host auf Port $port...<br>";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "<h2 style='color:green'>ERFOLG! Verbindung steht.</h2>";
    echo "Status Tabelle prüfen:<br>";
    $stmt = $pdo->query("SELECT * FROM public.auftragsstatus");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($result, true) . "</pre>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>FEHLER</h2>";
    echo "<strong>Die Datenbank sagt:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<h3>Mögliche Ursachen:</h3>";
    echo "<ul>";
    echo "<li><strong>Passwort falsch?</strong> (Prüfe es in Supabase unter Project Settings -> Database -> Reset password)</li>";
    echo "<li><strong>IPv4 Problem?</strong> Du nutzt Port 6543, das ist gut. Aber vielleicht blockiert eine Firewall.</li>";
    echo "<li><strong>SSL Fehler?</strong> Wenn da steht 'SSL certificate problem', fehlt deinem XAMPP/PHP ein Zertifikat.</li>";
    echo "</ul>";
}
?>