<?php
require_once "auth.php";
requireLoginJson();
require_once "db.php";
header("Content-Type: application/json");

function ok($p, $counts = null){ 
    $res = ["data" => $p];
    if($counts) $res["counts"] = $counts;
    echo json_encode($res); 
    exit; 
}
function bad($m,$c=400){ 
    http_response_code($c); 
    echo json_encode(["error"=>$m]); 
    exit; 
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    try {
        // Globale Zähler
        $stmtC = $pdo->query("SELECT status_id, count(*) as c FROM public.auftrag GROUP BY status_id");
        $counts = [1=>0, 2=>0, 3=>0, 4=>0, "total"=>0];
        foreach($stmtC->fetchAll() as $row) {
            $counts[(int)$row["status_id"]] = (int)$row["c"];
            $counts["total"] += (int)$row["c"];
        }

        // Mitarbeiter-Liste (DIREKTES ARRAY)
        if (isset($_GET["mitarbeiter"])) {
            $stmt = $pdo->query("SELECT mitarbeiterid, vorname, nachname FROM public.mitarbeiter WHERE aktiv=true ORDER BY nachname");
            echo json_encode($stmt->fetchAll());
            exit;
        }

        // Einzelauftrag für PDF
        if (isset($_GET["id"])) {
            $stmt = $pdo->prepare("SELECT a.*, s.status_name, m.vorname as m_v, m.nachname as m_n, k.vorname as k_v, k.nachname as k_n, k.telefon_privat, k.telefon_mobil FROM public.auftrag a LEFT JOIN public.kunde k ON a.kunde_id = k.kunde_id LEFT JOIN public.auftragsstatus s ON a.status_id = s.status_id LEFT JOIN public.mitarbeiter m ON a.mitarbeiterid = m.mitarbeiterid WHERE a.auftrag_id = :id");
            $stmt->execute([":id"=>(int)$_GET["id"]]);
            $r = $stmt->fetch();
            if(!$r) bad("Nicht gefunden", 404);
            echo json_encode($r);
            exit;
        }

        // Dashboard-Liste
        $statusid = isset($_GET["statusid"]) ? (int)$_GET["statusid"] : null;
        $q = trim($_GET["q"] ?? "");
        $where = []; 
        $params = [];
        
        if ($statusid) { 
            $where[] = "a.status_id = :sid"; 
            $params[":sid"] = $statusid; 
        }
        if ($q !== "") { 
            $where[] = "(a.auftragsnummer ILIKE :q OR a.objekt_adresse ILIKE :q OR k.vorname ILIKE :q OR k.nachname ILIKE :q)"; 
            $params[":q"] = "%$q%"; 
        }
        
        $whereSql = count($where) ? "WHERE ".implode(" AND ", $where) : "";

        $stmt = $pdo->prepare("SELECT a.auftrag_id, a.auftragsnummer, a.objekt_adresse, a.beschreibung, a.erfasst_am, a.status_id, s.status_name, a.termin, a.mitarbeiterid, m.vorname as m_v, m.nachname as m_n, k.vorname as k_v, k.nachname as k_n FROM public.auftrag a LEFT JOIN public.kunde k ON a.kunde_id = k.kunde_id LEFT JOIN public.auftragsstatus s ON a.status_id = s.status_id LEFT JOIN public.mitarbeiter m ON a.mitarbeiterid = m.mitarbeiterid $whereSql ORDER BY a.erfasst_am DESC");
        $stmt->execute($params);
        ok($stmt->fetchAll(), $counts);

    } catch (PDOException $e) { 
        bad($e->getMessage(), 500); 
    }
}

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if(!$data) bad("Keine Daten erhalten");
    
    try {
        $pdo->beginTransaction();
        $parts = explode(" ", $data["customer"] ?? "", 2);
        $stmtK = $pdo->prepare("INSERT INTO public.kunde (vorname, nachname, telefon_privat, telefon_mobil, typ) VALUES (:v,:n,:t,:m,'Privat') RETURNING kunde_id");
        $stmtK->execute([":v" => $parts[0] ?? '-', ":n" => $parts[1] ?? '-', ":t" => $data["phone"] ?? null, ":m" => $data["mobile"] ?? null]);
        $kid = $stmtK->fetchColumn();
        
        $nr = "A-" . date("Y") . "-" . str_pad(rand(1,999), 3, "0", STR_PAD_LEFT);
        $stmtA = $pdo->prepare("INSERT INTO public.auftrag (auftragsnummer, kunde_id, objekt_adresse, beschreibung, status_id, leistung, art) VALUES (:nr,:kid,:adr,:desc,1,:l,:a) RETURNING auftrag_id");
        $stmtA->execute([":nr" => $nr, ":kid" => $kid, ":adr" => $data["objAddr"], ":desc" => $data["desc"], ":l" => $data["leistung"] ?? null, ":a" => $data["art"] ?? null]);
        
        $pdo->commit();
        echo json_encode(["status" => "success", "id" => $stmtA->fetchColumn()]);
    } catch (Exception $e) { 
        $pdo->rollBack(); 
        bad($e->getMessage()); 
    }
    exit;
}

if ($method === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);
    if(!$data || !isset($data["id"])) bad("Fehlende Daten");
    
    try {
        $sid = (int)$data["statusid"];
        $sql = "UPDATE public.auftrag SET status_id = :sid";
        $params = [":sid" => $sid, ":id" => (int)$data["id"]];
        
        if($sid === 2 && isset($data["termin"]) && isset($data["mitarbeiterid"])) {
            $sql .= ", termin = :t, mitarbeiterid = :mid";
            $params[":t"] = $data["termin"];
            $params[":mid"] = (int)$data["mitarbeiterid"];
        }
        
        $sql .= " WHERE auftrag_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) { 
        bad($e->getMessage(), 500); 
    }
    exit;
}

bad("Methode nicht erlaubt", 405);
