# KFZ Tax Calculator DE - Version 1.5

## Überblick
WordPress Plugin zur Berechnung der deutschen KFZ-Steuer nach aktuellen Gesetzen (2025). Das Plugin unterstützt **alle Fahrzeugtypen** (Benziner, Diesel, Hybrid, Elektro) mit korrekten Steuerberechnungen und verfügt über eine vollständige WordPress Admin-Oberfläche.

## Neue Features (Version 1.5)

### 🔧 **UI/UX Verbesserungen**
- **Wankel-Motor entfernt**: Wankelmotoren werden jetzt automatisch als Benziner behandelt (steuerlich identisch)
- **Elektroauto-Optimierung**: CO2- und Hubraum-Felder werden bei Elektroautos automatisch ausgegraut und deaktiviert
- **Informative Hinweise**: Spezielle Hinweisbox für Elektrofahrzeuge erklärt, warum bestimmte Felder nicht relevant sind
- **Verbesserte Benutzerführung**: Klarere Kennzeichnung welche Felder für welchen Fahrzeugtyp relevant sind

### ⚡ **Elektrofahrzeug-Spezifika**
- **Automatische Felddeaktivierung**: Hubraum und CO2 werden auf 0 gesetzt und sind nicht editierbar
- **Visuelle Kennzeichnung**: Deaktivierte Felder sind deutlich als solche erkennbar
- **Kontextuelle Hilfe**: Erklärung warum diese Felder für E-Autos irrelevant sind

## Features (Version 1.3)

### 🚗 **Vollständige Fahrzeugtyp-Unterstützung**
- **Benziner PKW**: Korrekte Hubraumsteuer (2,00€/100ccm) + gestaffelte CO2-Steuer
- **Diesel PKW**: Höhere Hubraumsteuer (9,50€/100ccm) + gleiche CO2-Steuer wie Benziner
- **Plug-in-Hybrid**: Berechnung wie Benziner (keine Steuerbefreiung, aber oft niedrigere CO2-Werte)
- **Elektroautos**: Steuerbefreiung bis 31.12.2030 für Erstzulassung 2021-2025

### 📊 **Korrekte Steuerberechnung 2025**
- **CO2-Staffelung**: 2,00€ - 4,00€ pro g/km über 95 g/km Freibetrag
- **Erstzulassungsjahr**: Berücksichtigung für Elektroauto-Befreiung
- **Mindeststeuer**: 20€ für alle konventionellen Fahrzeuge
- **Gewicht**: Optional, nur für Wohnmobile/Nutzfahrzeuge relevant

### 🧪 **Umfassende Tests**
- Test-Suite für alle Fahrzeugtypen
- Validierung gegen offizielle Steuersätze
- CO2-Staffelung Verifikation

## Features (Version 1.2)

### 🎛️ **WordPress Admin-Interface**
- **Dashboard**: Übersicht mit Statistiken und Plugin-Informationen
- **Anfragen & Logs**: Detaillierte Analyse aller Berechnungen mit Paginierung
- **Dokumentation**: Automatische Anzeige der Plugin-Dokumentation
- **DSGVO-konform**: Anonymisierte IP-Adressen und Datenschutz-Features

### 📊 **Erweiterte Analytics**
- Statistiken nach Zeiträumen (heute, diese Woche, dieser Monat)
- Aufschlüsselung nach Fahrzeugtypen mit Icons
- Vollständige Anfragen-Historie mit Such- und Filterfunktionen
- Export-Möglichkeit für Log-Dateien

### 🔧 **Admin-Funktionen**
- Logs löschen mit Sicherheitsabfrage
- Log-Datei herunterladen
- Plugin-Status und Versionsinformationen
- Shortcode-Anleitung für Administratoren

## Verbesserungen (Version 1.1)

### ✅ Korrekte Steuerlogik für Benziner PKWs
- **Hubraumsteuer**: 2,00 € je angefangene 100 ccm (korrekt implementiert)
- **CO2-Steuer**: Gestaffelte Berechnung ab 95 g/km nach aktuellen Sätzen 2025
- **Mindeststeuer**: 20 € für konventionelle Fahrzeuge
- **Berechnungslogik**: Vollständig überarbeitet und getestet

### 📊 CO2-Steuersätze 2025 (korrekt implementiert)
- 96-115 g/km: 2,00 € pro g/km
- 116-135 g/km: 2,20 € pro g/km  
- 136-155 g/km: 2,50 € pro g/km
- 156-175 g/km: 2,90 € pro g/km
- 176-195 g/km: 3,40 € pro g/km
- über 195 g/km: 4,00 € pro g/km

### 🔧 Code-Verbesserungen
- Optimierte `calculateKfzSteuer()` Funktion in `calculate-tax.php`
- Erweiterte `KFZ_Tax_Calculator` Klasse mit besserer Struktur
- Separate CO2-Steuer Berechnungsmethode für bessere Wartbarkeit
- Verbesserte Fehlerbehandlung und Validierung

### 🧪 Testing
- Umfassende Test-Suite für verschiedene Benziner-Szenarien
- Validierung der Berechnungen gegen offizielle Steuersätze
- Test-Datei: `test-benziner-standalone.php`

## Beispiel-Berechnungen

### 1.6L Benziner mit 120 g/km CO2 (Erstzulassung 2023)
- **Hubraumsteuer**: 1600 ccm ÷ 100 = 16 × 2,00 € = 32,00 €
- **CO2-Steuer**: (120-95) g/km = 25 g/km
  - 20 g/km × 2,00 € = 40,00 €
  - 5 g/km × 2,20 € = 11,00 €
  - Gesamt CO2-Steuer: 51,00 €
- **Gesamtsteuer**: 32,00 € + 51,00 € = **83,00 € pro Jahr**
- **Monatlich**: 6,92 €

### Weitere Test-Szenarien
- Kleiner Benziner (1.0L, 90 g/km): 20,00 € (Mindeststeuer)
- Großer Benziner (3.0L, 180 g/km): 269,00 €
- Sportwagen (4.0L, 220 g/km): 440,00 €

## Installation
1. Plugin-Ordner in `/wp-content/plugins/` hochladen
2. Plugin in WordPress aktivieren
3. Shortcode `[kfz_tax_form]` in Seiten/Beiträge einfügen

## Verwendung
Das Plugin stellt ein benutzerfreundliches Formular bereit:
- Fahrzeugtyp auswählen (Benzin, Diesel, Hybrid, Elektro)
- Hubraum in ccm eingeben
- CO2-Ausstoß in g/km eingeben
- Erstzulassungsjahr eingeben
- Zulässiges Gesamtgewicht in kg eingeben *(optional - nur für Wohnmobile/Nutzfahrzeuge relevant)*

### 💡 Wichtiger Hinweis zum Gewicht
Das Gewicht ist nur für **Wohnmobile und Nutzfahrzeuge** steuerrelevant. Für normale PKW wird die Steuer ausschließlich nach **Hubraum und CO₂-Ausstoß** berechnet, entsprechend den aktuellen deutschen Steuergesetzen.

## Technische Details

### Dateien
- `kfz-tax-calculator.php`: Haupt-Plugin-Datei mit Shortcode
- `calculate-tax.php`: Backend-API für Steuerberechnung
- `includes/class-kfz-tax-calculator.php`: Hauptklasse mit Berechnungslogik
- `test-benziner-standalone.php`: Test-Suite für Validierung

### Features
- AJAX-basierte Berechnung ohne Seitenreload
- Responsive Design für mobile Geräte
- FontAwesome Icons für Fahrzeugtypen
- DSGVO-konforme Protokollierung
- Detaillierte Steueraufschlüsselung

### Kompatibilität
- WordPress 4.7+
- PHP 7.0+
- Alle modernen Browser

## Rechtlicher Hinweis
Diese Berechnung erfolgt nach den aktuellen Steuersätzen für 2025. Die tatsächliche Steuer kann je nach weiteren Faktoren variieren. Für verbindliche Auskünfte wenden Sie sich an Ihr Finanzamt.

## Changelog

### Version 1.3 (2025) - **Vollständige Fahrzeugtyp-Unterstützung**
- ✅ **Umfassende Websuche** durchgeführt für aktuelle KFZ-Steuer-Gesetze 2025
- ✅ **Benziner PKW**: Korrekte Hubraumsteuer (2,00€/100ccm) + gestaffelte CO2-Steuer
- ✅ **Diesel PKW**: Höhere Hubraumsteuer (9,50€/100ccm) + gleiche CO2-Steuer wie Benziner
- ✅ **Plug-in-Hybrid**: Berechnung wie Benziner (KEINE Steuerbefreiung mehr)
- ✅ **Elektroautos**: Steuerbefreiung bis 31.12.2030 für Erstzulassung 2021-2025
- ✅ **Erstzulassungsjahr-abhängige Berechnung**:
  - **Vor Juli 2009**: Schadstoffklassen-Berechnung (Euro 0-6)
  - **Nov 2008 - Juni 2009**: Günstigerprüfung (alte vs. neue Berechnung)
  - **Juli 2009 - 2020**: CO2-Steuer ab 120 g/km (2,00€ pro g/km)
  - **Ab 2021**: CO2-Steuer ab 95 g/km, gestaffelt (2,00€ - 4,00€ pro g/km)
- ✅ **CO2-Staffelung 2025** korrekt implementiert:
  - 96-115 g/km: 2,00€ pro g/km
  - 116-135 g/km: 2,20€ pro g/km
  - 136-155 g/km: 2,50€ pro g/km
  - 156-175 g/km: 2,90€ pro g/km
  - 176-195 g/km: 3,40€ pro g/km
  - über 195 g/km: 4,00€ pro g/km
- ✅ **Gewicht optional** - nur für Wohnmobile/Nutzfahrzeuge steuerrelevant
- ✅ **Elektroauto-Validierung** - Hubraum 0 ist erlaubt
- ✅ **Erstzulassungsjahr-Logik** für korrekte Elektroauto-Befreiung
- ✅ **Umfassende Test-Suite** mit allen Fahrzeugtypen (`test-all-vehicles-v1.3.php`)
- ✅ **Erstzulassungsjahr-Tests** (`test-erstzulassung-years.php`)
- ✅ **Berechnungsbeispiele** in README dokumentiert
- ✅ **Plugin-Header** auf Version 1.3 aktualisiert
- ✅ **Admin-Interface** zeigt Version 1.3 an
- ✅ **Installationsbereite ZIP-Datei** erstellt

### Version 1.2 (2025)
- ✅ **WordPress Admin-Interface** komplett implementiert
- ✅ **Dashboard** mit Statistiken und Plugin-Informationen
- ✅ **Anfragen & Logs** Seite mit detaillierter Analyse
- ✅ **Dokumentation** Seite mit automatischer README-Anzeige
- ✅ **DSGVO-konforme** Datenverarbeitung und IP-Anonymisierung
- ✅ **Paginierung** für große Datenmengen (50 Einträge pro Seite)
- ✅ **Admin-Funktionen**: Logs löschen und herunterladen
- ✅ **Responsive Design** für alle Admin-Seiten
- ✅ **Saubere Code-Struktur** mit separaten Template-Dateien
- ✅ **Sicherheitsfeatures**: Nonce-Verifikation und Capability-Checks

### Version 1.1 (2025)
- ✅ Korrekte CO2-Steuersätze 2025 implementiert
- ✅ Optimierte Berechnungslogik für Benziner PKWs
- ✅ Erweiterte Klassen-Struktur
- ✅ Umfassende Test-Suite hinzugefügt
- ✅ Verbesserte Frontend-Darstellung
- ✅ Code-Dokumentation erweitert

### Version 1.0
- Grundlegende KFZ-Steuer Berechnung
- WordPress Plugin Struktur
- Shortcode Implementation