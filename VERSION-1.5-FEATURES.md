# KFZ Tax Calculator DE - Version 1.5 Features

## 🎯 Hauptverbesserungen

### 1. Wankel-Motor entfernt
- **Grund**: Steuerlich identisch mit Benzinern
- **Änderung**: Button aus UI entfernt, Backend behandelt Wankelmotoren automatisch als Benziner
- **Vorteil**: Vereinfachte Benutzeroberfläche, weniger Verwirrung

### 2. Elektroauto-Optimierung
- **Automatische Felddeaktivierung**: CO2 und Hubraum werden bei E-Auto-Auswahl ausgegraut
- **Visuelle Kennzeichnung**: Deaktivierte Felder sind deutlich erkennbar
- **Informative Hinweisbox**: Erklärt warum diese Felder irrelevant sind
- **Intelligente Validierung**: Required-Attribute werden dynamisch angepasst

## 🔧 Technische Details

### UI-Verbesserungen
```css
/* Neue CSS-Klassen für deaktivierte Felder */
.kfz-tax-form-grid input[type="number"]:disabled {
    background-color: #e9ecef;
    color: #6c757d;
    border-color: #ced4da;
    cursor: not-allowed;
}

.electric-info {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    /* Wird nur bei E-Auto-Auswahl angezeigt */
}
```

### JavaScript-Funktionalität
```javascript
// Dynamische Feldsteuerung basierend auf Fahrzeugtyp
function toggleFieldsBasedOnVehicleType() {
    // Automatische Aktivierung/Deaktivierung
    // Hinweisbox ein-/ausblenden
    // Required-Attribute verwalten
}
```

## 📦 Verfügbare Downloads

1. **kfz-tax-calculator-version-1.5-final.zip** (27KB)
   - Produktionsbereit, ohne Test-Dateien
   - Empfohlen für WordPress-Installation

2. **kfz-tax-calculator-version-1.5.zip** (124KB)
   - Vollversion mit allen Test-Dateien
   - Für Entwicklung und Testing

## 🚀 Installation

1. ZIP-Datei herunterladen
2. In WordPress Admin → Plugins → Installieren → ZIP hochladen
3. Plugin aktivieren
4. Shortcode `[kfz_tax_form]` in Seite/Beitrag einfügen

## ✅ Getestete Funktionen

- ✅ Wankel-Button entfernt
- ✅ Elektroauto-Felder werden automatisch deaktiviert
- ✅ Hinweisbox erscheint bei E-Auto-Auswahl
- ✅ Steuerberechnung funktioniert für alle Fahrzeugtypen
- ✅ Responsive Design bleibt erhalten
- ✅ Admin-Interface funktioniert weiterhin

## 🔄 Migration von v1.4

- Keine Datenbankänderungen erforderlich
- Bestehende Shortcodes funktionieren weiterhin
- Logs und Einstellungen bleiben erhalten
- Einfaches Plugin-Update möglich