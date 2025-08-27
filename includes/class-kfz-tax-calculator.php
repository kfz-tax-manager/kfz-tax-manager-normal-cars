<?php

/**
 * KFZ Tax Calculator Klasse - Version 1.5 (Korrigiert nach offiziellen Daten)
 * 
 * Berechnet die deutsche KFZ-Steuer nach aktuellen Gesetzen (2025)
 * Unterstützt alle Fahrzeugtypen: Benziner, Diesel, Hybrid, Elektro
 * 
 * @author Mano Kors & NicVW
 * @version 1.5
 */

// Sicherheitscheck: Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

class KFZ_Tax_Calculator {
    
    /**
     * Plugin initialisieren
     */
    public static function init() {
        // Plugin ist bereit
    }
    
    /**
     * Hauptberechnungsmethode
     * 
     * @param array $data Eingabedaten
     * @return array Berechnungsergebnis oder Fehler
     */
    public static function calculate($data) {
        // Pflichtfelder prüfen (Gewicht ist optional)
        $required_fields = ['type', 'displacement', 'co2_emission', 'first_registration_year'];
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                return ['error' => "Fehlendes Feld: $field"];
            }
        }

        $vehicle_type = $data['type'];
        $displacement = (int) $data['displacement'];
        $co2_emission = (int) $data['co2_emission'];
        $first_registration_year = (int) $data['first_registration_year'];
        $weight = isset($data['weight']) ? (int) $data['weight'] : 0;
        $euro_norm = isset($data['euro_norm']) ? $data['euro_norm'] : null;

        // Wertebereich prüfen (Elektroautos haben Hubraum 0)
        if ($vehicle_type !== 'elektro' && ($displacement < 1 || $displacement > 10000)) {
            return ['error' => 'Hubraum muss zwischen 1 und 10000 ccm liegen'];
        }
        if ($vehicle_type === 'elektro' && ($displacement < 0 || $displacement > 10000)) {
            return ['error' => 'Hubraum für Elektroautos muss 0 oder zwischen 1 und 10000 ccm liegen'];
        }
        if ($co2_emission < 0 || $co2_emission > 500) {
            return ['error' => 'CO2-Ausstoß muss zwischen 0 und 500 g/km liegen'];
        }
        if ($first_registration_year < 1990 || $first_registration_year > (int)date('Y')) {
            return ['error' => 'Erstzulassung muss zwischen 1990 und aktuellem Jahr liegen'];
        }
        if ($weight > 0 && ($weight < 100 || $weight > 50000)) {
            return ['error' => 'Gewicht muss zwischen 100 und 50000 kg liegen'];
        }
        if (!in_array($vehicle_type, ['benzin', 'diesel', 'elektro', 'hybrid'], true)) {
            return ['error' => 'Ungültiger Fahrzeugtyp'];
        }

        return self::calculateKfzSteuer($vehicle_type, $displacement, $co2_emission, $first_registration_year, $weight, $euro_norm);
    }

    /**
     * Berechnet die KFZ-Steuer nach deutschen Gesetzen (2025)
     * 
     * @param string $vehicle_type Fahrzeugtyp (benzin, diesel, hybrid, elektro)
     * @param int $displacement Hubraum in ccm
     * @param int $co2_emission CO2-Ausstoß in g/km
     * @param int $first_registration_year Erstzulassungsjahr
     * @param int $weight Gewicht in kg (optional, nur für Wohnmobile relevant)
     * @return array Berechnungsergebnis
     */
    private static function calculateKfzSteuer($vehicle_type, $displacement, $co2_emission, $first_registration_year, $weight = 0, $euro_norm = null) {
        $displacement_tax = 0.0;
        $co2_tax = 0.0;
        $base_tax = 0.0;
        $info = '';
        
        // Erstzulassungsjahr bestimmt die Berechnungsmethode
        $registration_date = new DateTime($first_registration_year . '-01-01');
        $july_2009 = new DateTime('2009-07-01');
        $nov_2008 = new DateTime('2008-11-05');
        $june_2009 = new DateTime('2009-06-30');
        $jan_2021 = new DateTime('2021-01-01');
        
        switch ($vehicle_type) {
            case 'benzin':
            case 'hybrid': // Hybrid wird wie Benziner behandelt
                if ($registration_date < $july_2009) {
                    // Vor 1. Juli 2009: Berechnung nach Schadstoffklasse
                    return self::calculateOldTaxSystem('benzin', $displacement, $first_registration_year, $nov_2008, $june_2009, $euro_norm);
                } else {
                    // Ab 1. Juli 2009: Hubraum + CO2-Berechnung
                    $displacement_tax = ceil($displacement / 100) * 2.00;
                    
                    if ($registration_date >= $jan_2021) {
                        // Ab 2021: Neue gestaffelte CO2-Sätze ab 95g/km
                        if ($co2_emission > 95) {
                            $co2_tax = self::calculateCO2Tax($co2_emission);
                        }
                    } else {
                        // 2009-2020: CO2-Berechnung mit variablen Freibeträgen
                        $co2_tax = self::calculateOldCO2Tax($co2_emission, $first_registration_year);
                    }
                    
                    $info = $vehicle_type === 'hybrid' ? 
                        'Plug-in-Hybrid: Berechnung wie Benziner. Keine Steuerbefreiung.' :
                        ($registration_date >= $jan_2021 ? 
                            'Benziner PKW (ab 2021): Hubraumsteuer (2,00€/100ccm) + gestaffelte CO₂-Steuer ab 95 g/km.' :
                            'Benziner PKW (2009-2020): Hubraumsteuer (2,00€/100ccm) + CO₂-Steuer (2,00€ pro g/km über variablen Freibetrag).');
                }
                break;
                
            case 'diesel':
                if ($registration_date < $july_2009) {
                    // Vor 1. Juli 2009: Berechnung nach Schadstoffklasse
                    return self::calculateOldTaxSystem('diesel', $displacement, $first_registration_year, $nov_2008, $june_2009, $euro_norm);
                } else {
                    // Ab 1. Juli 2009: Hubraum + CO2-Berechnung
                    $displacement_tax = ceil($displacement / 100) * 9.50;
                    
                    if ($registration_date >= $jan_2021) {
                        // Ab 2021: Neue gestaffelte CO2-Sätze ab 95g/km
                        if ($co2_emission > 95) {
                            $co2_tax = self::calculateCO2Tax($co2_emission);
                        }
                    } else {
                        // 2009-2020: CO2-Berechnung mit variablen Freibeträgen
                        $co2_tax = self::calculateOldCO2Tax($co2_emission, $first_registration_year);
                    }
                    
                    $info = $registration_date >= $jan_2021 ? 
                        'Diesel PKW (ab 2021): Hubraumsteuer (9,50€/100ccm) + gestaffelte CO₂-Steuer ab 95 g/km.' :
                        'Diesel PKW (2009-2020): Hubraumsteuer (9,50€/100ccm) + CO₂-Steuer (2,00€ pro g/km über variablen Freibetrag).';
                }
                break;
                
            case 'elektro':
                // Elektrofahrzeuge: Steuerbefreiung je nach Erstzulassung
                if ($first_registration_year >= 2021 && $first_registration_year <= 2025) {
                    $displacement_tax = 0.0;
                    $co2_tax = 0.0;
                    $base_tax = 0.0;
                    $info = 'Elektrofahrzeuge (Erstzulassung 2021-2025) sind bis 31.12.2030 steuerbefreit.';
                } elseif ($first_registration_year >= 2026 && $first_registration_year <= 2030) {
                    $displacement_tax = 0.0;
                    $co2_tax = 0.0;
                    $base_tax = 0.0;
                    $info = 'Elektrofahrzeuge (Erstzulassung 2026-2030) sind bis 31.12.2030 steuerbefreit.';
                } elseif ($first_registration_year >= 2031) {
                    // Nach 2030: Gewichtsbasierte Besteuerung wie Nutzfahrzeuge mit 50% Ermäßigung
                    $weight_tax = self::calculateElectricCarTax($weight);
                    $base_tax = $weight_tax * 0.5;
                    $info = 'Elektrofahrzeuge (ab 2031): Gewichtsbasierte Besteuerung wie Nutzfahrzeuge mit 50% Ermäßigung.';
                } else {
                    $displacement_tax = 0.0;
                    $co2_tax = 0.0;
                    $base_tax = 0.0;
                    $info = 'Elektrofahrzeuge (Erstzulassung vor 2021) sind in der Regel steuerbefreit.';
                }
                break;
        }
        
        $annual_tax = $displacement_tax + $co2_tax + $base_tax;
        $monthly_tax = $annual_tax / 12;
        
        return [
            'annual' => round($annual_tax, 2),
            'monthly' => round($monthly_tax, 2),
            'details' => [
                'displacement_tax' => round($displacement_tax, 2),
                'co2_tax' => round($co2_tax, 2),
                'base_tax' => round($base_tax, 2)
            ],
            'info' => $info
        ];
    }

    /**
     * Berechnet die CO2-Steuer für den Zeitraum 2009-2020 mit variablen Freibeträgen
     * KORREKT nach offiziellen Daten:
     * - Bis 31.12.2011: 120 g/km steuerfrei
     * - Ab 01.01.2012: 110 g/km steuerfrei  
     * - Ab 01.01.2014: 95 g/km steuerfrei
     * 
     * @param int $co2_emission CO2-Ausstoß in g/km
     * @param int $first_registration_year Erstzulassungsjahr
     * @return float CO2-Steuer in Euro
     */
    private static function calculateOldCO2Tax($co2_emission, $first_registration_year) {
        // Variable Freibeträge je nach Erstzulassungsjahr (OFFIZIELLE DATEN)
        if ($first_registration_year <= 2011) {
            $free_allowance = 120;
        } elseif ($first_registration_year <= 2013) {
            $free_allowance = 110;
        } else {
            $free_allowance = 95;
        }
        
        return max(0, ($co2_emission - $free_allowance)) * 2.00;
    }

    /**
     * Berechnet die CO2-Steuer nach aktuellen Sätzen (ab 2021)
     * Gestaffelte Berechnung ab 95 g/km
     * 
     * @param int $co2_emission CO2-Ausstoß in g/km
     * @return float CO2-Steuer in Euro
     */
    private static function calculateCO2Tax($co2_emission) {
        if ($co2_emission <= 95) {
            return 0.0;
        }
        
        $co2_tax = 0.0;
        $remaining_co2 = $co2_emission - 95;
        
        // Steuersätze 2025 (gestaffelt) - OFFIZIELLE DATEN
        $tax_brackets = [
            ['min' => 0,   'max' => 20,  'rate' => 2.00], // über 95 bis 115 g/km
            ['min' => 20,  'max' => 40,  'rate' => 2.20], // über 115 bis 135 g/km
            ['min' => 40,  'max' => 60,  'rate' => 2.50], // über 135 bis 155 g/km
            ['min' => 60,  'max' => 80,  'rate' => 2.90], // über 155 bis 175 g/km
            ['min' => 80,  'max' => 100, 'rate' => 3.40], // über 175 bis 195 g/km
            ['min' => 100, 'max' => PHP_INT_MAX, 'rate' => 4.00] // über 195 g/km
        ];
        
        foreach ($tax_brackets as $bracket) {
            if ($remaining_co2 <= 0) break;
            
            $bracket_amount = min($remaining_co2, $bracket['max'] - $bracket['min']);
            $co2_tax += $bracket_amount * $bracket['rate'];
            $remaining_co2 -= $bracket_amount;
        }
        
        return $co2_tax;
    }
    
    /**
     * Berechnet die Steuer für Elektroautos basierend auf Gewicht
     * (für Fahrzeuge ab 2031) - OFFIZIELLE DATEN: Wie Nutzfahrzeuge
     * 
     * @param int $weight Gewicht in kg
     * @return float Steuer in Euro
     */
    private static function calculateElectricCarTax($weight) {
        if ($weight <= 0) {
            // Fallback: Durchschnittliches E-Auto Gewicht
            $weight = 1800;
        }
        
        // Gewichtsbasierte Besteuerung wie bei Nutzfahrzeugen (OFFIZIELLE DATEN)
        $weight_units = ceil($weight / 200);
        
        // Steuersätze je angefangene 200kg (wie Nutzfahrzeuge bis 3.500kg)
        if ($weight <= 2000) {
            return $weight_units * 11.25;
        } elseif ($weight <= 3000) {
            $first_units = ceil(2000 / 200);
            $remaining_units = $weight_units - $first_units;
            return ($first_units * 11.25) + ($remaining_units * 12.02);
        } else {
            $first_units = ceil(2000 / 200);
            $second_units = ceil(1000 / 200);
            $remaining_units = $weight_units - $first_units - $second_units;
            return ($first_units * 11.25) + ($second_units * 12.02) + ($remaining_units * 12.78);
        }
    }
    
    /**
     * Berechnet die KFZ-Steuer nach dem alten System (vor Juli 2009)
     * Basierend auf Hubraum und Schadstoffklasse - OFFIZIELLE DATEN
     * 
     * @param string $vehicle_type Fahrzeugtyp
     * @param int $displacement Hubraum in ccm
     * @param int $first_registration_year Erstzulassungsjahr
     * @param DateTime $nov_2008 5. November 2008
     * @param DateTime $june_2009 30. Juni 2009
     * @return array Berechnungsergebnis
     */
    private static function calculateOldTaxSystem($vehicle_type, $displacement, $first_registration_year, $nov_2008, $june_2009, $euro_norm = null) {
        $registration_date = new DateTime($first_registration_year . '-01-01');
        
        // Schadstoffklasse: Vom Benutzer gewählt oder geschätzt basierend auf Erstzulassungsjahr
        $emission_class = $euro_norm ?: self::estimateEmissionClass($first_registration_year);
        
        // OFFIZIELLE Steuersätze nach Schadstoffklasse (Euro pro 100ccm)
        $tax_rates = [
            'benzin' => [
                'Euro 6' => 6.75, 'Euro 5' => 6.75, 'Euro 4' => 6.75, 'Euro 3' => 6.75,
                'Euro 2' => 7.36, 'Euro 1' => 15.13, 
                'Euro 0_clean' => 21.07, // Euro-0 (ehemals ohne Ozonfahrverbot)
                'Euro 0' => 25.36 // Euro-0 (übrige)
            ],
            'diesel' => [
                'Euro 6' => 15.44, 'Euro 5' => 15.44, 'Euro 4' => 15.44, 'Euro 3' => 15.44,
                'Euro 2' => 16.05, 'Euro 1' => 27.35,
                'Euro 0_clean' => 33.29, // Euro-0 (ehemals ohne Ozonfahrverbot)
                'Euro 0' => 37.58 // Euro-0 (übrige)
            ]
        ];
        
        // Steuersatz für die gewählte Euro-Norm
        $rate = $tax_rates[$vehicle_type][$emission_class] ?? $tax_rates[$vehicle_type]['Euro 0'];
        
        // Hubraum-Multiplikator: Hubraum durch 100, aufgerundet
        $hubraum_multiplikator = ceil($displacement / 100);
        
        // Steuer berechnen
        $old_tax = $hubraum_multiplikator * $rate;
        
        // Auf vollen Euro abrunden (im Sinne des Halters)
        $old_tax = floor($old_tax);
        
        // Günstigerprüfung für Fahrzeuge zwischen 5.11.2008 und 30.6.2009
        if ($registration_date >= $nov_2008 && $registration_date <= $june_2009) {
            // Auch neue CO2-basierte Berechnung durchführen
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
        $euro_norm_display = $emission_class;
        if ($emission_class === 'Euro 0_clean') {
            $euro_norm_display = 'Euro 0 (ehemals ohne Ozonfahrverbot)';
        } elseif ($emission_class === 'Euro 0') {
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
    
    /**
     * Schätzt die Schadstoffklasse basierend auf dem Erstzulassungsjahr
     * 
     * @param int $year Erstzulassungsjahr
     * @return string Schadstoffklasse
     */
    private static function estimateEmissionClass($year) {
        if ($year >= 2015) return 'Euro 6';
        if ($year >= 2011) return 'Euro 5';
        if ($year >= 2006) return 'Euro 4';
        if ($year >= 2001) return 'Euro 3';
        if ($year >= 1997) return 'Euro 2';
        if ($year >= 1993) return 'Euro 1';
        return 'Euro 0';
    }

    /**
     * Berechnet die Wohnmobil-Steuer basierend auf Gewicht und Schadstoffklasse
     * 
     * @param int $weight Gewicht in kg
     * @param string $emission_class Schadstoffklasse (Euro 6, Euro 5, etc.)
     * @return float Steuer in Euro
     */
    public static function calculateCamperTax($weight, $emission_class = 'Euro 6') {
        // Steuersätze für Wohnmobile (pro angefangene 200kg)
        $tax_rates = [
            'Euro 6' => 10.00,
            'Euro 5' => 16.00,
            'Euro 4' => 25.00,
            'Euro 3' => 33.00,
            'Euro 2' => 40.00,
            'Euro 1' => 40.00,
            'other' => 40.00
        ];
        
        $rate = $tax_rates[$emission_class] ?? $tax_rates['other'];
        $weight_units = ceil($weight / 200);
        
        return $weight_units * $rate;
    }
}
?>