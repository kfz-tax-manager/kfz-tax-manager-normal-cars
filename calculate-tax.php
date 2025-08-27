
<?php

/**
 * KFZ-Steuer Rechner Backend (2025)
 *
 * Berechnet die deutsche KFZ-Steuer nach aktuellen Gesetzen (2025).
 * Loggt alle Anfragen für spätere Analysen (DSGVO-konform, keine Speicherung von personenbezogenen Daten über das technisch Notwendige hinaus).
 *
 * @author 2025
 * @see https://www.gesetze-im-internet.de/kraftstg_2002/  (KraftStG)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');


// Preflight-Request für CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit(0);
}


// Nur POST-Requests zulassen
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_response('Method not allowed', 405);
}


// JSON-Input parsen
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    error_response('Invalid JSON input', 400);
}


// Pflichtfelder prüfen (Gewicht ist optional)
$required_fields = ['type', 'displacement', 'co2_emission', 'first_registration_year'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        error_response("Fehlendes Pflichtfeld: $field", 400);
    }
}


// Eingabewerte extrahieren und validieren
$vehicle_type = $input['type'];
$displacement = (int) $input['displacement'];
$co2_emission = (int) $input['co2_emission'];
$first_registration_year = (int) $input['first_registration_year'];
$weight = isset($input['weight']) ? (int) $input['weight'] : 0; // Gewicht ist optional
$euro_norm = isset($input['euro_norm']) ? $input['euro_norm'] : null; // Euro-Norm für Fahrzeuge vor 2009


// Wertebereich prüfen (Elektroautos haben Hubraum 0)
if ($vehicle_type !== 'elektro' && ($displacement < 1 || $displacement > 10000)) {
    error_response('Hubraum muss zwischen 1 und 10000 ccm liegen', 400);
}
if ($vehicle_type === 'elektro' && ($displacement < 0 || $displacement > 10000)) {
    error_response('Hubraum für Elektroautos muss 0 oder zwischen 1 und 10000 ccm liegen', 400);
}
if ($co2_emission < 0 || $co2_emission > 500) {
    error_response('CO2-Ausstoß muss zwischen 0 und 500 g/km liegen', 400);
}
if ($first_registration_year < 1990 || $first_registration_year > (int)date('Y')) {
    error_response('Erstzulassung muss zwischen 1990 und aktuellem Jahr liegen', 400);
}
if ($weight > 0 && ($weight < 100 || $weight > 50000)) {
    error_response('Gewicht muss zwischen 100 und 50000 kg liegen', 400);
}
if (!in_array($vehicle_type, ['benzin', 'diesel', 'elektro', 'hybrid'], true)) {
    error_response('Ungültiger Fahrzeugtyp', 400);
}

/**
 * Gibt eine standardisierte JSON-Fehlermeldung aus und beendet das Skript.
 * @param string $msg
 * @param int $code
 * @return never
 */
function error_response(string $msg, int $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $msg]);
    exit;
}


/**
 * Berechnet die deutsche KFZ-Steuer nach aktuellem Recht (2025).
 * Optimiert für korrekte Benziner PKW Steuerberechnung.
 *
 * @param string $vehicle_type   Fahrzeugtyp ('benzin', 'diesel', 'elektro', 'hybrid')
 * @param int    $displacement   Hubraum in ccm
 * @param int    $co2_emission   CO2-Ausstoß in g/km
 * @param int    $first_registration_year Erstzulassung (Jahr)
 * @param int    $weight         Leergewicht in kg
 * @return array                 Steuerdaten (jährlich, monatlich, Aufschlüsselung)
 */
function calculateKfzSteuer(string $vehicle_type, int $displacement, int $co2_emission, int $first_registration_year, int $weight, ?string $euro_norm = null): array {
    $displacement_tax = 0.0;
    $co2_tax = 0.0;
    $base_tax = 0.0;
    $hybrid_discount = 0.0;

    // Elektrofahrzeuge sind steuerbefreit (Stand 2025)
    if ($vehicle_type === 'elektro') {
        return [
            'annual' => 0.0,
            'monthly' => 0.0,
            'details' => [
                'displacement_tax' => 0.0,
                'co2_tax' => 0.0,
                'base_tax' => 0.0
            ],
            'info' => 'Elektrofahrzeuge sind bis 2030 steuerbefreit.'
        ];
    }

    // Fahrzeuge vor Juli 2009: Berechnung nach Schadstoffklasse
    if ($first_registration_year <= 2009 && $euro_norm) {
        return calculateOldTaxSystem($vehicle_type, $displacement, $euro_norm, $first_registration_year);
    }

    // Hubraumsteuer berechnen (Grundsteuer)
    if ($vehicle_type === 'benzin' || $vehicle_type === 'hybrid') {
        // Benziner und Hybrid: 2,00 € je angefangene 100 ccm
        $displacement_tax = ceil($displacement / 100) * 2.00;
    } elseif ($vehicle_type === 'diesel') {
        // Diesel: 9,50 € je angefangene 100 ccm
        $displacement_tax = ceil($displacement / 100) * 9.50;
    }

    // CO2-Steuer berechnen (für Fahrzeuge ab 1. Juli 2009)
    // Neue gestaffelte CO2-Besteuerung gilt für Fahrzeuge ab 1. Januar 2021
    if ($first_registration_year >= 2009) {
        $co2_threshold = 95; // g/km Freibetrag
        if ($first_registration_year >= 2021) {
            $co2_threshold = 95;
            // La logique pour les véhicules à partir de 2021 est gérée par les tarifs progressifs
        } elseif ($first_registration_year >= 2014) {
            $co2_threshold = 95;
            $co2_rate = 2.00;
        } elseif ($first_registration_year >= 2012) {
            $co2_threshold = 110;
            $co2_rate = 2.00;
        } elseif ($first_registration_year >= 2009) {
            $co2_threshold = 120;
            $co2_rate = 2.00;
        }

        if ($first_registration_year >= 2021) {
            if ($co2_emission > $co2_threshold) {
                $co2_over_threshold = $co2_emission - $co2_threshold;
                
                if ($co2_over_threshold <= 20) { // 95-115 g/km
                    $co2_tax = $co2_over_threshold * 2.00;
                } elseif ($co2_over_threshold <= 40) { // 115-135 g/km
                    $co2_tax = 20 * 2.00 + ($co2_over_threshold - 20) * 2.20;
                } elseif ($co2_over_threshold <= 60) { // 135-155 g/km
                    $co2_tax = 20 * 2.00 + 20 * 2.20 + ($co2_over_threshold - 40) * 2.50;
                } elseif ($co2_over_threshold <= 80) { // 155-175 g/km
                    $co2_tax = 20 * 2.00 + 20 * 2.20 + 20 * 2.50 + ($co2_over_threshold - 60) * 2.90;
                } elseif ($co2_over_threshold <= 100) { // 175-195 g/km
                    $co2_tax = 20 * 2.00 + 20 * 2.20 + 20 * 2.50 + 20 * 2.90 + ($co2_over_threshold - 80) * 3.40;
                } else { // über 195 g/km
                    $co2_tax = 20 * 2.00 + 20 * 2.20 + 20 * 2.50 + 20 * 2.90 + 20 * 3.40 + ($co2_over_threshold - 100) * 4.00;
                }
            }
        } else {
            // Logique de taxation simple pour les véhicules immatriculés entre 2009 et 2020
            if ($co2_emission > $co2_threshold) {
                $co2_tax = ($co2_emission - $co2_threshold) * $co2_rate;
            }
        }
    }

    // Hybridfahrzeuge erhalten Steuerermäßigung (50% Rabatt auf Gesamtsteuer)
    if ($vehicle_type === 'hybrid') {
        $total_before_discount = $displacement_tax + $co2_tax;
        $hybrid_discount = $total_before_discount * 0.5;
        $base_tax = $total_before_discount - $hybrid_discount;
        $displacement_tax = 0.0;
        $co2_tax = 0.0;
    }

    // Gesamtsteuer berechnen
    $total_tax = $base_tax + $displacement_tax + $co2_tax;
    
    // Mindeststeuer für konventionelle Fahrzeuge (20 €)
    if ($total_tax < 20 && $vehicle_type !== 'elektro') {
        $base_tax = 20.0;
        $displacement_tax = 0.0;
        $co2_tax = 0.0;
        $total_tax = 20.0;
    }

    // Ergebnis zusammenstellen
    $result = [
        'annual' => floor($total_tax),
        'monthly' => round($total_tax / 12, 2),
        'details' => [
            'displacement_tax' => round($displacement_tax, 2),
            'co2_tax' => round($co2_tax, 2),
            'base_tax' => round($base_tax, 2)
        ]
    ];
    
    // Zusätzliche Informationen für spezielle Fahrzeugtypen
    if ($vehicle_type === 'hybrid') {
        $result['hybrid_discount'] = round($hybrid_discount, 2);
        $result['info'] = 'Hybridfahrzeuge erhalten 50% Steuerermäßigung auf die Gesamtsteuer.';
    } elseif ($vehicle_type === 'benzin') {
        $result['info'] = 'Benziner PKW: Hubraumsteuer (2€/100ccm) + CO₂-Steuer (gestaffelt ab 95 g/km).';
    }
    
    return $result;
}

/**
 * Berechnet die KFZ-Steuer nach dem alten System (vor Juli 2009)
 * Basierend auf Hubraum und Schadstoffklasse
 * 
 * @param string $vehicle_type Fahrzeugtyp
 * @param int $displacement Hubraum in ccm
 * @param string $euro_norm Euro-Norm
 * @param int $first_registration_year Erstzulassungsjahr
 * @return array Berechnungsergebnis
 */
function calculateOldTaxSystem(string $vehicle_type, int $displacement, string $euro_norm, int $first_registration_year): array {
    // Steuersätze nach Schadstoffklasse (Euro pro 100ccm) - aus der offiziellen Dokumentation
    $tax_rates = [
        'benzin' => [
            'Euro 6' => 6.75, 'Euro 5' => 6.75, 'Euro 4' => 6.75, 'Euro 3' => 6.75,
            'Euro 2' => 7.36, 'Euro 1' => 15.13, 
            'Euro 0_clean' => 21.07, // Euro-0 (ehemals ohne Ozonfahrverbot)
            'Euro 0' => 25.36 // Euro-0 (sonstige)
        ],
        'diesel' => [
            'Euro 6' => 15.44, 'Euro 5' => 15.44, 'Euro 4' => 15.44, 'Euro 3' => 15.44,
            'Euro 2' => 16.05, 'Euro 1' => 27.35,
            'Euro 0_clean' => 33.29, // Euro-0 (ehemals ohne Ozonfahrverbot)
            'Euro 0' => 37.58 // Euro-0 (sonstige)
        ]
    ];
    
    // Steuersatz für die gewählte Euro-Norm
    $rate = $tax_rates[$vehicle_type][$euro_norm] ?? $tax_rates[$vehicle_type]['Euro 0'];
    
    // Hubraum-Multiplikator: Hubraum durch 100, aufgerundet
    $hubraum_multiplikator = ceil($displacement / 100);
    
    // Steuer berechnen
    $old_tax = $hubraum_multiplikator * $rate;
    
    // Auf vollen Euro abrunden (im Sinne des Halters)
    $old_tax = floor($old_tax);
    
    // Günstigerprüfung für Fahrzeuge zwischen 5.11.2008 und 30.6.2009
    $registration_date = new DateTime($first_registration_year . '-01-01');
    $nov_2008 = new DateTime('2008-11-05');
    $june_2009 = new DateTime('2009-06-30');
    
    if ($registration_date >= $nov_2008 && $registration_date <= $june_2009) {
        // Auch neue CO2-basierte Berechnung durchführen (vereinfacht mit Annahme)
        $new_displacement_tax = ceil($displacement / 100) * ($vehicle_type === 'diesel' ? 9.50 : 2.00);
        $new_co2_tax = max(0, (150 - 120)) * 2.00; // Annahme: 150g CO2, 120g Freibetrag für 2008/2009
        $new_tax = $new_displacement_tax + $new_co2_tax;
        
        // Günstigere Variante wählen
        if ($new_tax < $old_tax) {
            return [
                'annual' => round($new_tax, 2),
                'monthly' => round($new_tax / 12, 2),
                'details' => [
                    'displacement_tax' => round($new_displacement_tax, 2),
                    'co2_tax' => round($new_co2_tax, 2),
                    'base_tax' => 0.0
                ],
                'info' => 'Günstigerprüfung: Neue CO2-basierte Berechnung angewendet (günstiger als Schadstoffklassen-Berechnung).'
            ];
        }
    }
    
    // Euro-Norm-Namen für die Anzeige aufbereiten
    $euro_norm_display = $euro_norm;
    if ($euro_norm === 'Euro 0_clean') {
        $euro_norm_display = 'Euro 0 (ehemals ohne Ozonfahrverbot)';
    } elseif ($euro_norm === 'Euro 0') {
        $euro_norm_display = 'Euro 0 (sonstige)';
    }
    
    return [
        'annual' => $old_tax,
        'monthly' => round($old_tax / 12, 2),
        'details' => [
            'displacement_tax' => $old_tax,
            'co2_tax' => 0.0,
            'base_tax' => 0.0
        ],
        'info' => "Alte Berechnung (vor Juli 2009): {$euro_norm_display}, {$rate}€ pro 100ccm. Hubraum-Multiplikator: {$hubraum_multiplikator} (aufgerundet von " . ($displacement/100) . ")."
    ];
}


// Steuer berechnen
$result = calculateKfzSteuer($vehicle_type, $displacement, $co2_emission, $first_registration_year, $weight, $euro_norm);

// Logging (DSGVO: nur technisch notwendige Daten, keine Speicherung von Namen, Adressen o.ä.)
$logData = [
    'timestamp' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    'input' => $input,
    'result' => $result
];
$logLine = json_encode($logData, JSON_UNESCAPED_UNICODE) . "\n";
@file_put_contents(__DIR__ . '/requests.log', $logLine, FILE_APPEND | LOCK_EX);

// JSON-Antwort zurückgeben
echo json_encode($result);
?>