# KFZ Tax Calculator - WordPress Admin Interface

## Übersicht der Admin-Oberfläche

Das Plugin verfügt über eine vollständige WordPress Admin-Oberfläche mit drei Hauptbereichen:

### 🏠 **Übersicht** (`/wp-admin/admin.php?page=kfz-tax-calculator`)

**Dashboard mit wichtigen Kennzahlen:**
- Gesamtanzahl der Berechnungsanfragen
- Anfragen heute
- Plugin-Version und Status
- Letzte 5 Berechnungen in Tabellenform
- Plugin-Informationen (Shortcode, unterstützte Fahrzeugtypen, Steuersätze)

**Features:**
- Übersichtliche Statistik-Karten
- Schnellzugriff auf detaillierte Logs
- Plugin-Informationen für Administratoren

### 📊 **Anfragen & Logs** (`/wp-admin/admin.php?page=kfz-tax-calculator-logs`)

**Detaillierte Analyse aller Berechnungen:**
- Erweiterte Statistiken (Gesamt, Heute, Diese Woche, Dieser Monat)
- Aufschlüsselung nach Fahrzeugtypen mit Icons
- Vollständige Tabelle aller Anfragen mit Paginierung (50 pro Seite)
- DSGVO-konforme IP-Anonymisierung (letzte 2 Stellen maskiert)

**Tabellen-Spalten:**
- Zeitpunkt (dd.mm.yyyy hh:mm:ss)
- Fahrzeugtyp (mit Icons: ⛽🚗🌱⚡)
- Hubraum (ccm)
- CO2-Ausstoß (g/km)
- Erstzulassungsjahr
- Gewicht (kg)
- Berechnete Steuer (jährlich/monatlich)
- Anonymisierte IP-Adresse
- User Agent (gekürzt)

**Admin-Funktionen:**
- ✅ **Logs löschen** (mit Sicherheitsabfrage)
- ✅ **Log-Datei herunterladen** (direkter Download)
- ✅ **Paginierung** für große Datenmengen

### 📚 **Dokumentation** (`/wp-admin/admin.php?page=kfz-tax-calculator-docs`)

**Vollständige Plugin-Dokumentation:**
- Automatische Anzeige der README.md Datei
- Markdown-zu-HTML Konvertierung
- Professionelles Styling für optimale Lesbarkeit
- Alle Plugin-Features und Verbesserungen dokumentiert

## Menü-Integration

Das Plugin fügt ein neues Hauptmenü **"KFZ Steuer"** in der WordPress Admin-Sidebar hinzu:

```
🧮 KFZ Steuer
├── 📊 Übersicht
├── 📋 Anfragen & Logs  
└── 📚 Dokumentation
```

**Menü-Icon:** `dashicons-calculator`
**Position:** 30 (nach "Seiten")
**Berechtigung:** `manage_options` (nur Administratoren)

## Technische Details

### Dateistruktur
```
kfz-tax-calculator-version-1.1./
├── kfz-tax-calculator.php          # Haupt-Plugin-Datei
├── includes/
│   ├── class-kfz-tax-calculator.php # Berechnungslogik
│   ├── admin-pages.php              # Admin-Funktionen
│   └── admin-templates/             # Template-Dateien
│       ├── overview.php             # Übersichts-Template
│       ├── logs.php                 # Logs-Template
│       └── documentation.php        # Dokumentations-Template
├── calculate-tax.php                # Backend API
├── requests.log                     # Log-Datei (automatisch erstellt)
└── README.md                        # Plugin-Dokumentation
```

### Sicherheitsfeatures
- ✅ **ABSPATH-Prüfung** in allen Dateien
- ✅ **Nonce-Verifikation** für kritische Aktionen
- ✅ **Capability-Checks** (`manage_options`)
- ✅ **Input-Sanitization** mit `esc_html()`
- ✅ **DSGVO-konforme Datenverarbeitung**

### Responsive Design
- ✅ **CSS Grid Layout** für optimale Darstellung
- ✅ **WordPress Admin-Styling** Integration
- ✅ **Mobile-optimiert** für Tablets und Smartphones
- ✅ **Konsistente Farbgebung** mit WordPress-Theme

## Installation & Aktivierung

1. **Plugin hochladen:** Ordner nach `/wp-content/plugins/` kopieren
2. **Plugin aktivieren:** In WordPress Admin unter "Plugins"
3. **Admin-Menü:** Automatisch verfügbar unter "KFZ Steuer"
4. **Shortcode verwenden:** `[kfz_tax_form]` in Seiten/Beiträge einfügen

## Logging & Datenschutz

**Automatisches Logging:**
- Jede Berechnung wird in `requests.log` protokolliert
- JSON-Format für einfache Auswertung
- Zeitstempel, anonymisierte IP, Eingabedaten, Ergebnisse

**DSGVO-Konformität:**
- Keine Speicherung von Namen oder Adressen
- IP-Adressen werden anonymisiert angezeigt
- Nur technisch notwendige Daten
- Admin kann Logs jederzeit löschen

## Beispiel Log-Eintrag
```json
{
  "timestamp": "2025-01-11T08:30:15+01:00",
  "ip": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...",
  "input": {
    "type": "benzin",
    "displacement": 1600,
    "co2_emission": 120,
    "first_registration_year": 2023,
    "weight": 1400
  },
  "result": {
    "annual": 83.0,
    "monthly": 6.92,
    "details": {
      "displacement_tax": 32.0,
      "co2_tax": 51.0,
      "base_tax": 0.0
    }
  }
}
```

Die Admin-Oberfläche bietet Administratoren vollständige Kontrolle und Einblick in die Plugin-Nutzung, während gleichzeitig alle Datenschutzbestimmungen eingehalten werden.