<?php
require_once "auth.php";
$loggedIn = isset($_SESSION["user"]);
$user = $_SESSION["user"] ?? null;

function isAdmin() {
    global $user;
    if (!$user) return false;
    return (($user["rolle_name"] ?? "") === "Admin" || (int)($user["rolle_id"] ?? 0) === 1);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Glauser AG - Service Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { background-color:#f8f9fa; font-family:Segoe UI, sans-serif; }
        .sidebar { min-height:100vh; background:#343a40; color:#fff; }
        .nav-link { color:#adb5bd; }
        .nav-link.active { color:#fff; background:#495057; border-radius:5px; }
        .stats-card { transition:transform 0.2s; cursor: pointer; border-width: 2px; }
        .stats-card:hover { transform:translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .stats-card.active { border-width: 4px !important; }
        .status-badge { width:110px; display:inline-block; text-align:center; font-size:.75rem; }
    </style>
</head>
<body>

<?php if (!$loggedIn): ?>
    <!-- LOGIN VIEW -->
    <div class="container py-5">
        <div class="row justify-content-center py-5">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold text-center">Glauser AG - Login</div>
                    <div class="card-body">
                        <div id="loginMsg" class="alert alert-danger d-none"></div>
                        <div class="mb-3"><label class="form-label">Username</label><input class="form-control" id="loginUser"></div>
                        <div class="mb-3"><label class="form-label">Passwort</label><input type="password" class="form-control" id="loginPass"></div>
                        <button class="btn btn-primary w-100" id="btnLogin">Login</button>
                        <button class="btn btn-outline-secondary w-100 mt-2" id="btnOpenRegister">Registrieren</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Registrieren</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div id="regMsg" class="alert alert-danger d-none"></div><div class="mb-2"><label class="form-label">Vorname</label><input class="form-control" id="rVorname"></div><div class="mb-2"><label class="form-label">Nachname</label><input class="form-control" id="rNachname"></div><div class="mb-2"><label class="form-label">Username</label><input class="form-control" id="rUsername"></div><div class="mb-2"><label class="form-label">Passwort</label><input type="password" class="form-control" id="rPassword"></div></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button><button class="btn btn-primary" id="btnRegister">Account erstellen</button></div></div></div></div>

<?php else: ?>
    <!-- APP VIEW -->
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar p-3 shadow">
                <h3 class="text-center mb-4">Glauser AG</h3>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a href="#" class="nav-link active" id="navDash"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#orderModal"><i class="fas fa-plus-circle me-2"></i>Neuer Auftrag</a></li>
                    <li class="nav-item mb-2"><a href="#" class="nav-link" id="navArchiv"><i class="fas fa-history me-2"></i>Archiv</a></li>
                </ul>
            </nav>

            <main class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Service-Dashboard</h2>
                    <button class="btn btn-outline-secondary shadow-sm" id="btnLogout">Logout</button>
                </div>

                <div class="row mb-4 text-center">
                    <div class="col"><div class="card stats-card border-dark p-3 active" id="card-all" onclick="filterByStatus(null)"><h6>Alle</h6><h2 id="count-total">0</h2></div></div>
                    <div class="col"><div class="card stats-card border-warning p-3" id="card-1" onclick="filterByStatus(1)"><h6>Offen</h6><h2 id="count-1">0</h2></div></div>
                    <div class="col"><div class="card stats-card border-info p-3" id="card-2" onclick="filterByStatus(2)"><h6>Disponiert</h6><h2 id="count-2">0</h2></div></div>
                    <div class="col"><div class="card stats-card border-success p-3" id="card-3" onclick="filterByStatus(3)"><h6>Ausgeführt</h6><h2 id="count-3">0</h2></div></div>
                    <div class="col"><div class="card stats-card border-secondary p-3" id="card-4" onclick="filterByStatus(4)"><h6>Verrechnet</h6><h2 id="count-4">0</h2></div></div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between"><b>Auftragsliste</b><input class="form-control form-control-sm w-25" id="q" placeholder="Suchen..."></div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Nr</th><th>Kunde</th><th>Adresse</th><th>Status</th><th>Termin</th><th>Mitarbeiter</th><th class="text-end">Aktionen</th></tr></thead>
                            <tbody id="tbody"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="orderModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form id="orderForm"><div class="modal-header bg-primary text-white"><h5>Serviceauftrag erfassen</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body row g-3">
        <div class="col-md-6"><label class="form-label">Kunde</label><input class="form-control" id="customer" required></div>
        <div class="col-md-3"><label class="form-label">Telefon</label><input class="form-control" id="phone"></div>
        <div class="col-md-3"><label class="form-label">Natel</label><input class="form-control" id="mobile"></div>
        <div class="col-md-12"><label class="form-label">Objekt-Adresse</label><input class="form-control" id="objAddr" required></div>
        <div class="col-md-6"><label class="form-label">Leistung</label><select class="form-select" id="leistung"><option value="">(wahlweise)</option><option value="Sanitär">Sanitär</option><option value="Heizung">Heizung</option></select></div>
        <div class="col-md-6"><label class="form-label">Art</label><select class="form-select" id="art"><option value="">(wahlweise)</option><option value="Reparatur">Reparatur</option><option value="Garantie">Garantie</option></select></div>
        <div class="col-md-12"><label class="form-label">Beschreibung</label><textarea class="form-control" id="desc" rows="3" required></textarea></div>
    </div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Abbrechen</button><button class="btn btn-primary" type="submit">Speichern</button></div></form></div></div></div>

    <div class="modal fade" id="dispoModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="dispoForm"><div class="modal-header bg-info text-white"><h5>Disponieren</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" id="dispoId"><div id="dispoMsg" class="alert alert-danger d-none"></div><div class="mb-3"><label class="form-label">Termin</label><input type="datetime-local" class="form-control" id="termin" required></div><div class="mb-3"><label class="form-label">Mitarbeiter</label><select class="form-select" id="mitarbeiter" required></select></div></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Abbrechen</button><button class="btn btn-info text-white" type="submit">Speichern</button></div></form></div></div></div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    let statusFilter = null;

    // --- DASHBOARD: Aufträge laden ---
    async function loadOrders() {
        const qField = document.getElementById("q");
        const search = qField ? qField.value : "";
        const url = `api.php?view=dashboard${statusFilter ? '&statusid='+statusFilter : ''}${search ? '&q='+search : ''}`;
        
        try {
            const res = await fetch(url);
            const json = await res.json();
            const orders = json.data || [];
            const counts = json.counts || {};

            // Zähler oben updaten
            if(document.getElementById("count-total")) {
                document.getElementById("count-total").textContent = counts.total || 0;
                document.getElementById("count-1").textContent = counts[1] || 0;
                document.getElementById("count-2").textContent = counts[2] || 0;
                document.getElementById("count-3").textContent = counts[3] || 0;
                document.getElementById("count-4").textContent = counts[4] || 0;
            }

            const tbody = document.getElementById("tbody");
            if(!tbody) return;
            tbody.innerHTML = "";

            orders.forEach(o => {
                const tr = document.createElement("tr");
                const stName = (o.status_name || "Offen").toUpperCase();
                let bCls = "bg-warning text-dark";
                if(o.status_id == 2) bCls = "bg-info"; 
                if(o.status_id == 3) bCls = "bg-success"; 
                if(o.status_id == 4) bCls = "bg-secondary";

                tr.innerHTML = `
                    <td>${o.auftragsnummer}</td>
                    <td><strong>${o.k_v} ${o.k_n}</strong></td>
                    <td><small>${o.objekt_adresse}</small></td>
                    <td><span class="badge ${bCls} status-badge">${stName}</span></td>
                    <td><small>${o.termin ? new Date(o.termin).toLocaleString("de-CH") : "-"}</small></td>
                    <td><small>${o.m_n ? o.m_n + " " + o.m_v : "-"}</small></td>
                    <td class="text-end">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-info b-disp"><i class="fas fa-calendar-alt"></i></button>
                            <button class="btn btn-sm btn-outline-success b-exec"><i class="fas fa-check"></i></button>
                            <button class="btn btn-sm btn-outline-secondary b-bill"><i class="fas fa-file-invoice-dollar"></i></button>
                            <button class="btn btn-sm btn-danger b-pdf"><i class="fas fa-file-pdf"></i></button>
                        </div>
                    </td>`;

                // Event-Listener sauber per JS zuweisen
                tr.querySelector(".b-disp").onclick = () => openDispo(o.auftrag_id, o.termin, o.mitarbeiterid);
                tr.querySelector(".b-exec").onclick = () => updateStatus(o.auftrag_id, 3);
                tr.querySelector(".b-bill").onclick = () => updateStatus(o.auftrag_id, 4);
                tr.querySelector(".b-pdf").onclick = () => generatePDF(o.auftrag_id);
                
                tbody.appendChild(tr);
            });
        } catch(e) { console.error("Load failed", e); }
    }

    // --- FUNKTIONEN ---
    async function updateStatus(id, sid) { 
        await fetch("api.php", {method:"PUT", headers:{"Content-Type":"application/json"}, body:JSON.stringify({id, statusid:sid})}); 
        loadOrders(); 
    }

    async function openDispo(id, termin, mid) {
        document.getElementById("dispoId").value = id;
        if (termin && termin !== 'null') document.getElementById("termin").value = termin.substring(0,16);
        const mRes = await fetch("api.php?mitarbeiter=1");
        const mList = await mRes.json();
        const sel = document.getElementById("mitarbeiter");
        sel.innerHTML = "";
        mList.forEach(m => { const opt = document.createElement("option"); opt.value = m.mitarbeiterid; opt.textContent = m.nachname + " " + m.vorname; sel.appendChild(opt); });
        if (mid) sel.value = mid;
        new bootstrap.Modal(document.getElementById("dispoModal")).show();
    }

    async function generatePDF(id) {
        const res = await fetch("api.php?id=" + id);
        const o = await res.json();
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        let y = 20;
        doc.setFontSize(22); doc.setFont("helvetica", "bold"); doc.text("SERVICEAUFTRAG", 105, y, {align:"center"});
        y += 10; doc.setLineWidth(0.5); doc.line(20, y, 190, y); y += 12;
        doc.setFontSize(10); doc.setFont("helvetica", "normal");
        doc.text("Datum: " + new Date(o.erfasst_am).toLocaleDateString("de-CH"), 20, y);
        doc.text("Zeit: " + new Date(o.erfasst_am).toLocaleTimeString("de-CH", {hour:"2-digit", minute:"2-digit"}), 160, y);
        y += 15;
        const row = (label, val) => { doc.setFont("helvetica", "bold"); doc.text(label, 20, y); doc.setFont("helvetica", "normal"); doc.text(String(val||"-"), 65, y); y += 8; };
        row("Kunde/Kontaktperson:", o.k_v + " " + o.k_n); row("Telefon:", o.telefon_privat); row("Natel:", o.telefon_mobil); row("Adresse Objekt:", o.objekt_adresse);
        y += 5; row("Adresse Verrechnung:", "Herr Rudolf Rutishauser\nUnterdorfstrasse 22\n8484 Weisslingen");
        y += 10; doc.line(20, y, 190, y); y += 10;
        doc.setFont("helvetica", "bold"); doc.text("Auszuführende Arbeiten:", 20, y);
        const cb = (x, t, c) => { doc.rect(x, y-3, 4, 4); if(c) doc.text("X", x+0.8, y+0.5); doc.setFont("helvetica", "normal"); doc.text(t, x+6, y); };
        cb(65, "Reparatur", (o.art||"").includes("Reparatur")); cb(110, "Sanitär", (o.leistung||"").includes("Sanitär")); y += 8;
        cb(65, "Heizung", (o.leistung||"").includes("Heizung")); cb(110, "Garantie", (o.art||"").includes("Garantie")); y += 15;
        doc.setFont("helvetica", "bold"); doc.text("Beschreibung:", 20, y);
        const lines = doc.splitTextToSize(o.beschreibung||"", 125); doc.text(lines, 65, y);
        y += (lines.length * 5) + 10; doc.line(20, y, 190, y); y += 10;
        row("Terminwunsch:", o.termin ? new Date(o.termin).toLocaleString("de-CH") : "so schnell wie möglich");
        doc.save("Serviceauftrag_" + o.auftragsnummer + ".pdf");
    }

    function filterByStatus(s) { statusFilter = s; document.querySelectorAll('.stats-card').forEach(c => c.classList.remove('active')); document.getElementById(s ? 'card-'+s : 'card-all').classList.add('active'); loadOrders(); }

    // --- EVENT HANDLER ---
    window.onload = () => {
        if(document.getElementById("orderForm")) {
            document.getElementById("orderForm").onsubmit = async (e) => {
                e.preventDefault();
                const payload = { customer: document.getElementById("customer").value, phone: document.getElementById("phone").value, mobile: document.getElementById("mobile").value, objAddr: document.getElementById("objAddr").value, desc: document.getElementById("desc").value, leistung: document.getElementById("leistung").value, art: document.getElementById("art").value };
                const res = await fetch("api.php", { method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(payload) });
                if(res.ok) { bootstrap.Modal.getInstance(document.getElementById("orderModal")).hide(); document.getElementById("orderForm").reset(); loadOrders(); }
            };
        }

        if(document.getElementById("dispoForm")) {
            document.getElementById("dispoForm").onsubmit = async (e) => {
                e.preventDefault();
                const p = { id: document.getElementById("dispoId").value, statusid: 2, termin: document.getElementById("termin").value, mitarbeiterid: document.getElementById("mitarbeiter").value };
                await fetch("api.php", {method:"PUT", headers:{"Content-Type":"application/json"}, body:JSON.stringify(p)});
                bootstrap.Modal.getInstance(document.getElementById("dispoModal")).hide(); loadOrders();
            };
        }

        if(document.getElementById("q")) document.getElementById("q").oninput = () => loadOrders();
        if(document.getElementById("btnLogout")) document.getElementById("btnLogout").onclick = async () => { await fetch("api_login.php?logout=1"); window.location.reload(); };

        <?php if (!$loggedIn): ?>
        if(document.getElementById("btnLogin")) {
            document.getElementById("btnLogin").onclick = async () => {
                const r = await fetch("api_login.php", {method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify({username:document.getElementById("loginUser").value, password:document.getElementById("loginPass").value})});
                if(r.ok) window.location.reload(); else alert("Login falsch");
            };
        }
        if(document.getElementById("btnOpenRegister")) document.getElementById("btnOpenRegister").onclick = () => new bootstrap.Modal(document.getElementById("registerModal")).show();
        if(document.getElementById("btnRegister")) {
            document.getElementById("btnRegister").onclick = async () => {
                const p = { vorname:document.getElementById("rVorname").value, nachname:document.getElementById("rNachname").value, username:document.getElementById("rUsername").value, password:document.getElementById("rPassword").value };
                await fetch("api_register.php", {method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify(p)});
                window.location.reload();
            };
        }
        <?php endif; ?>

        loadOrders();
    };
</script>
</body>
</html>
