# Changelog - KFZ Tax Calculator DE

## Version 1.5 (2025-01-XX)

### 🔧 UI/UX Verbesserungen
- **ENTFERNT**: Wankel-Motor Button aus der Benutzeroberfläche
  - Wankelmotoren werden steuerlich wie Benziner behandelt
  - Vereinfachung der Benutzeroberfläche auf die 4 Hauptfahrzeugtypen
  - Automatische Behandlung von Wankelmotoren als Benziner im Backend

### ⚡ Elektrofahrzeug-Optimierungen
- **NEU**: Automatische Felddeaktivierung für Elektroautos
  - CO2-Ausstoß und Hubraum-Felder werden automatisch ausgegraut
  - Felder werden auf 0 gesetzt und sind nicht editierbar
  - `required`-Attribute werden dynamisch entfernt/hinzugefügt

- **NEU**: Informative Hinweisbox für Elektrofahrzeuge
  - Erklärt warum CO2 und Hubraum für E-Autos irrelevant sind
  - Wird nur bei Auswahl von Elektroautos angezeigt
  - Verbesserte Benutzerführung und Verständnis

### 🎨 Design-Verbesserungen
- **NEU**: CSS-Styling für deaktivierte Felder
  - Grauer Hintergrund und Schrift für deaktivierte Eingabefelder
  - "not-allowed" Cursor für bessere UX
  - Konsistente visuelle Kennzeichnung

### 🔧 Technische Verbesserungen
- **VERBESSERT**: JavaScript-Logik für dynamische Feldsteuerung
- **VERBESSERT**: Submit-Handler berücksichtigt Elektroauto-Spezifika
- **AKTUALISIERT**: Plugin-Version auf 1.5
- **AKTUALISIERT**: Dokumentation und README

### 📦 Bereitstellung
- **NEU**: Saubere Produktions-ZIP ohne Test-Dateien
- **AKTUALISIERT**: Versionsnummern in allen relevanten Dateien

---

## Version 1.4 (2024-XX-XX)
- Wankel-Motor Unterstützung (entfernt in v1.5)
- Vollständige Fahrzeugtyp-Unterstützung
- Korrekte CO2-Staffelung
- Umfassende Test-Suite

## Version 1.3 (2024-XX-XX)
- Erweiterte Fahrzeugtyp-Unterstützung
- Korrekte Steuerberechnung 2025
- Umfassende Tests

## Version 1.2 (2024-XX-XX)
- WordPress Admin-Interface
- Erweiterte Analytics
- DSGVO-konforme Logs

## Version 1.1 (2024-XX-XX)
- Grundlegende Steuerberechnung
- Shortcode-Unterstützung
- Basis-UI