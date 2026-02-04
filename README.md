# Serviceauftrag-
Serviceauftrag von Maikel, Tim, Denis 
# M295 – Serviceauftrags-Verwaltung

Eine webbasierte Auftrags-Management-Applikation zur Verwaltung von Serviceaufträgen mit Kunden, Mitarbeitern und automatischer PDF-Generierung.

## Tech Stack

- **Backend:** PHP 8.x mit PostgreSQL (Supabase)
- **Frontend:** HTML, CSS (Bootstrap 5.3), JavaScript (Vanilla)
- **Bibliotheken:** jsPDF (PDF-Generierung), Bootstrap Icons
- **Server:** Apache (XAMPP)
- **Datenbank:** PostgreSQL (Supabase-hosted)

## Features

✅ **Auftragsverwaltung**
- Erfassen neuer Serviceaufträge mit Kundendaten
- Filter nach Status (Offen, Disponiert, Ausgeführt, Verrechnet)
- Echtzeit-Zähler für alle Auftrags-Stati

✅ **Disponierung**
- Zuweisung von Aufträgen an Mitarbeiter
- Terminplanung mit Datum/Uhrzeit-Auswahl
- Status-Updates über Workflow

✅ **PDF-Export**
- Automatische Generierung von Serviceauftrags-Dokumenten
- Layout mit Firmen-Briefkopf und allen Auftragsdaten
- Download als PDF

✅ **Rollen & Authentifizierung**
- Login-System mit Session-Management
- Rollen: Admin, Mitarbeiter
- Admin-Bereich für Benutzerverwaltung

## Voraussetzungen

- XAMPP (oder Apache + PHP 8.x)
- Supabase-Account mit PostgreSQL-Datenbank
- Moderner Browser (Chrome, Edge, Firefox)

## Installation

### 1. Repository klonen
```bash
git clone <https://github.com/Denis23414/Serviceauftrag->
cd m295
