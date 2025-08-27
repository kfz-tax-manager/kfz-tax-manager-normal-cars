<?php
/**
 * Plugin Name: KFZ Tax Calculator DE
 * Description: Calculates German car tax via a form and dynamically displays the result. Supports all vehicle types: Petrol, Diesel, Hybrid, Electric. Shortcode [kfz_tax_form]
 * Version: 1.5
 * Author: Mano Kors & NicVW
 * Text Domain: kfz-tax-calculator
 */

// Sicherheitscheck: Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

// Hauptklasse laden
require_once plugin_dir_path(__FILE__) . 'includes/class-kfz-tax-calculator.php';

// Admin-Seiten laden
require_once plugin_dir_path(__FILE__) . 'includes/admin-pages.php';

// Plugin initialisieren
add_action('plugins_loaded', ['KFZ_Tax_Calculator', 'init']);

// Shortcode registrieren
add_shortcode('kfz_tax_form', 'kfz_tax_form_shortcode');

// Admin-Menü hinzufügen
add_action('admin_menu', 'kfz_tax_calculator_admin_menu');

/**
 * Shortcode für das KFZ-Steuer Formular
 */
function kfz_tax_form_shortcode() {
    ob_start();
    ?>
    <style>
        /* Styling for the info note at the bottom of the result */
.info-note {
    display: block; /* Ensures it takes full width */
    margin-top: 20px;
    padding: 15px;
    background-color: #e0f8ff; /* Light blue background */
    color: #004085; /* Dark blue text */
    border: 1px solid #b8daff; /* Light blue border */
    border-radius: 8px;
    font-size: 14px;
    font-style: normal; /* Override italic default for <em> */
    line-height: 1.5;
    text-align: left; /* Align text to the left */
}
        /* Base styles */
        .kfz-calculator-container {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px; /* Adjust as needed */
            margin: 30px auto;
        }

        .kfz-calculator-container h2 {
            font-size: 24px;
            color: #333;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: bold;
        }

        .kfz-tax-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two columns */
            gap: 20px 30px; /* Row and column gap */
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .kfz-tax-form-grid label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .kfz-tax-form-grid input[type="number"],
        .kfz-tax-form-grid select {
            width: calc(100% - 20px); /* Adjust width considering padding */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #f9f9f9;
            font-size: 16px;
        }

        /* Disabled field styling for electric vehicles */
        .kfz-tax-form-grid input[type="number"]:disabled {
            background-color: #e9ecef;
            color: #6c757d;
            border-color: #ced4da;
            cursor: not-allowed;
        }

        .kfz-tax-form-grid label.disabled {
            color: #6c757d;
        }

        /* Vehicle Type Selection (Radio buttons styled as buttons) */
        .vehicle-type-selection {
            margin-bottom: 25px;
        }
        .vehicle-type-selection .radio-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); /* Responsive columns */
            gap: 15px;
            margin-top: 15px;
        }

        .vehicle-type-selection input[type="radio"] {
            display: none; /* Hide default radio button */
        }

        .vehicle-type-selection label {
            display: flex; /* Use flex for icon and text alignment */
            flex-direction: column; /* Stack icon and text */
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px 10px;
            text-align: center;
            cursor: pointer;
            font-size: 16px;
            color: #555;
            transition: all 0.2s ease-in-out;
            margin-bottom: 0; /* Override default label margin */
            font-weight: normal; /* Override default label font-weight */
            min-height: 80px; /* Ensure consistent height for buttons */
        }

        .vehicle-type-selection label i { /* Icon styling */
            font-size: 32px; /* Larger icon size */
            margin-bottom: 8px; /* Space between icon and text */
        }
        .vehicle-type-selection label.icon-benzin i { color: #5fbeff; /* Example color for petrol icon */ }
        .vehicle-type-selection label.icon-diesel i { color: red; /* Example color for diesel icon */ }
        .vehicle-type-selection label.icon-hybrid i { color: #a0d468; /* Example color for hybrid icon */ }
        .vehicle-type-selection label.icon-elektro i { color: #ffdc5f; /* Example color for electric icon */ }

        .vehicle-type-selection input[type="radio"]:checked + label {
            background-color: #e0f0ff; /* Light blue background for selected */
            border-color: #007bff; /* Blue border for selected */
            color: #007bff; /* Blue text for selected */
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
        }

        .vehicle-type-selection label:hover {
            background-color: #e9e9e9;
        }
        .vehicle-type-selection input[type="radio"]:checked + label:hover {
            background-color: #d0e8ff;
        }

        /* Calculate Button */
        #kfz-tax-form button[type="submit"] {
            display: block;
            width: 100%; /* Full width */
            padding: 15px 25px;
            background-color: #007bff; /* Blue */
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 25px;
        }

        #kfz-tax-form button[type="submit"]:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Result Section */
        #kfz-tax-result {
            background-color: #e6ffe6; /* Light green background */
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid #ccffcc;
            text-align: center; /* Center main title */
        }

        #kfz-tax-result h3 { /* Main result title */
            font-size: 22px;
            color: #333;
            margin-top: 0;
            margin-bottom: 20px;
        }

        #kfz-tax-result strong {
            color: #333;
        }

        #kfz-tax-result .result-main-info {
            display: flex;
            align-items: flex-end; /* Align "pro Jahr" at the bottom */
            justify-content: center; /* Center the amounts */
            margin-bottom: 15px;
            font-size: 32px;
            color: #28a745; /* Green for total tax */
            font-weight: bold;
        }

        #kfz-tax-result .result-main-info .amount {
            font-size: 48px; /* Larger for the main amount */
            line-height: 1; /* Adjust line height for alignment */
            margin-right: 10px;
        }

        #kfz-tax-result .result-main-info .unit {
            font-size: 20px;
            font-weight: normal;
            color: #555;
        }

        #kfz-tax-result .result-monthly {
            font-size: 18px;
            color: #555;
            margin-top: 5px;
            margin-bottom: 20px;
        }

        #kfz-tax-result h4 { /* Details section title */
            font-size: 18px;
            color: #333;
            margin-top: 0;
            margin-bottom: 15px;
            text-align: left; /* Align details title to left */
        }

        #kfz-tax-result .result-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two columns for details */
            gap: 10px 20px;
            border-top: 1px solid #c9f0c9;
            padding-top: 15px;
            text-align: left; /* Align details content to left */
        }
        #kfz-tax-result .result-details-grid div {
            padding: 5px 0;
        }
        #kfz-tax-result .result-details-grid strong {
             font-weight: normal; /* Less bold for sub-details */
        }


        #kfz-tax-result details {
            margin-top: 15px;
            border-top: 1px solid #c9f0c9;
            padding-top: 15px;
        }

        #kfz-tax-result summary {
            font-weight: bold;
            cursor: pointer;
            color: #007bff;
        }

        #kfz-tax-result em {
            display: block;
            margin-top: 15px;
            font-style: italic;
            color: #666;
            font-size: 14px;
        }

        /* Error message styling */
        #kfz-tax-result strong[style*="color:red"] {
            color: #dc3545 !important;
        }
        #kfz-tax-result strong[style*="color:red"]::before {
            content: '⚠️ ';
        }

        /* Electric vehicle info styling */
        .electric-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 12px;
            margin-top: 15px;
            font-size: 14px;
            color: #856404;
            display: none;
        }

        .electric-info.show {
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .kfz-tax-form-grid {
                grid-template-columns: 1fr; /* Single column on smaller screens */
            }
            .vehicle-type-selection .radio-group {
                grid-template-columns: 1fr; /* Stack buttons on smaller screens */
            }
        }
    </style>

    <div class="kfz-calculator-container">
        <h2>Fahrzeugtyp</h2> <form id="kfz-tax-form">
            <div class="vehicle-type-selection">
                <div class="radio-group">
                    <input type="radio" id="type-benzin" name="type" value="benzin">
                    <label for="type-benzin" class="icon-benzin"><i class="fas fa-gas-pump"></i> Benziner</label>

                    <input type="radio" id="type-diesel" name="type" value="diesel">
                    <label for="type-diesel" class="icon-diesel"><i class="fas fa-car-side"></i> Diesel</label>

                    <input type="radio" id="type-hybrid" name="type" value="hybrid">
                    <label for="type-hybrid" class="icon-hybrid"><i class="fas fa-leaf"></i> Hybrid</label>

                    <input type="radio" id="type-elektro" name="type" value="elektro">
                    <label for="type-elektro" class="icon-elektro"><i class="fas fa-bolt"></i> Elektro</label>
                </div>
            </div>

            <div class="electric-info" id="electric-info">
                <strong>ℹ️ Hinweis für Elektrofahrzeuge:</strong><br>
                Elektroautos haben keinen Hubraum und produzieren keine direkten CO₂-Emissionen. 
                Diese Felder werden daher automatisch auf 0 gesetzt und sind nicht editierbar.
            </div>

            <div class="kfz-tax-form-grid">
                <div>
                    <label for="displacement">Hubraum (ccm)</label>
                    <input type="number" id="displacement" name="displacement" min="1" max="10000" required>
                </div>

                <div>
                    <label for="co2_emission">CO2-Ausstoß (g/km)</label>
                    <input type="number" id="co2_emission" name="co2_emission" min="0" max="500" required>
                </div>

                <div>
                    <label for="first_registration_year">Erstzulassung (Jahr)</label>
                    <input type="number" id="first_registration_year" name="first_registration_year" min="1990" max="<?php echo date('Y'); ?>" required>
                </div>

                <div id="euro-norm-container" style="display: none;">
                    <label for="euro_norm">Euro-Norm (für Fahrzeuge vor Juli 2009)</label>
                    <select id="euro_norm" name="euro_norm">
                        <option value="">Bitte wählen...</option>
                        <option value="Euro 6">Euro 6</option>
                        <option value="Euro 5">Euro 5</option>
                        <option value="Euro 4">Euro 4</option>
                        <option value="Euro 3">Euro 3</option>
                        <option value="Euro 2">Euro 2</option>
                        <option value="Euro 1">Euro 1</option>
                        <option value="Euro 0_clean">Euro 0 (ehemals ohne Ozonfahrverbot)</option>
                        <option value="Euro 0">Euro 0 (sonstige)</option>
                    </select>
                </div>

                <div>
                    <label for="weight">Zul. Gesamtgewicht (kg) <span style="color: #666; font-weight: normal;">(optional)</span></label>
                    <input type="number" id="weight" name="weight" min="100" max="50000" placeholder="Nur für Wohnmobile/Vans relevant">
                </div>
            </div>

            <div style="background: #f0f8ff; border: 1px solid #b8daff; border-radius: 6px; padding: 15px; margin-top: 20px; font-size: 14px; color: #004085;">
                <strong>💡 Hinweis zum Gewicht:</strong><br>
                Das Gewicht ist nur für <strong>Wohnmobile und Nutzfahrzeuge</strong> steuerrelevant. 
                Für normale PKW wird die Steuer ausschließlich nach <strong>Hubraum und CO₂-Ausstoß</strong> berechnet.
            </div>

            <button type="submit">KFZ-Steuer berechnen</button>
        </form>

        <div id="kfz-tax-result" style="margin-top:20px;">
            <h3 style="margin-top:0;">Ihre KFZ-Steuer</h3>
            <p style="font-size: 1.2em; color: #666;">Bitte Daten eingeben und berechnen.</p>
        </div>
    </div>

    <script>
    // Function to handle field enabling/disabling based on vehicle type
    function toggleFieldsBasedOnVehicleType() {
        const vehicleTypeRadios = document.querySelectorAll('input[name="type"]');
        const displacementField = document.getElementById('displacement');
        const co2Field = document.getElementById('co2_emission');
        const displacementLabel = document.querySelector('label[for="displacement"]');
        const co2Label = document.querySelector('label[for="co2_emission"]');
        const electricInfo = document.getElementById('electric-info');
        
        vehicleTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'elektro') {
                    // Disable and clear fields for electric vehicles
                    displacementField.disabled = true;
                    displacementField.value = '0';
                    displacementField.removeAttribute('required');
                    displacementLabel.classList.add('disabled');
                    
                    co2Field.disabled = true;
                    co2Field.value = '0';
                    co2Field.removeAttribute('required');
                    co2Label.classList.add('disabled');
                    
                    // Show electric vehicle info
                    electricInfo.classList.add('show');
                } else {
                    // Enable fields for other vehicle types
                    displacementField.disabled = false;
                    displacementField.value = '';
                    displacementField.setAttribute('required', 'required');
                    displacementLabel.classList.remove('disabled');
                    
                    co2Field.disabled = false;
                    co2Field.value = '';
                    co2Field.setAttribute('required', 'required');
                    co2Label.classList.remove('disabled');
                    
                    // Hide electric vehicle info
                    electricInfo.classList.remove('show');
                }
                
                // Check if euro norm field should be shown
                toggleEuroNormField();
            });
        });
    }
    
    // Function to show/hide Euro norm field based on registration year
    function toggleEuroNormField() {
        const yearField = document.getElementById('first_registration_year');
        const euroNormContainer = document.getElementById('euro-norm-container');
        const euroNormField = document.getElementById('euro_norm');
        const vehicleTypeRadios = document.querySelectorAll('input[name="type"]');
        
        function checkAndToggle() {
            const year = parseInt(yearField.value);
            const selectedType = Array.from(vehicleTypeRadios).find(radio => radio.checked)?.value;
            
            // Show Euro norm field for non-electric vehicles registered before July 2009
            if (selectedType && selectedType !== 'elektro' && year && year <= 2009) {
                euroNormContainer.style.display = 'block';
                euroNormField.setAttribute('required', 'required');
            } else {
                euroNormContainer.style.display = 'none';
                euroNormField.removeAttribute('required');
                euroNormField.value = '';
            }
        }
        
        yearField.addEventListener('input', checkAndToggle);
        yearField.addEventListener('change', checkAndToggle);
        
        // Initial check
        checkAndToggle();
    }
    
    // Initialize field toggling when page loads
    document.addEventListener('DOMContentLoaded', function() {
        toggleFieldsBasedOnVehicleType();
        toggleEuroNormField();
    });

    document.getElementById('kfz-tax-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const data = {
            type: form.type.value,
            displacement: form.type.value === 'elektro' ? 0 : parseInt(form.displacement.value),
            co2_emission: form.type.value === 'elektro' ? 0 : parseInt(form.co2_emission.value),
            first_registration_year: parseInt(form.first_registration_year.value),
            weight: form.weight.value ? parseInt(form.weight.value) : 0,
            euro_norm: form.euro_norm ? form.euro_norm.value : null
        };

        const resultOut = document.getElementById('kfz-tax-result');
        resultOut.innerHTML = `<h3>Ihre KFZ-Steuer</h3><strong style="color:#007bff;">Berechne...</strong>`; // Loading state with title

        fetch('<?php echo plugin_dir_url(__FILE__); ?>calculate-tax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(json => {
            if (json.error) {
                resultOut.innerHTML = `<h3>Ihre KFZ-Steuer</h3><strong style="color:red">Fehler : ${json.error}</strong>`;
            } else {
                resultOut.innerHTML = `
                    <h3>Ihre KFZ-Steuer</h3>
                    <div class="result-main-info">
                        <span class="amount">${json.annual.toFixed(2)} €</span>
                        <span class="unit">pro Jahr</span>
                    </div>
                    <div class="result-monthly">${json.monthly.toFixed(2)} € monatlich</div>
                    <h4>Steueraufschlüsselung:</h4>
                    <div class="result-details-grid">
                        <div>Hubraumsteuer:</div> <div>${json.details.displacement_tax.toFixed(2)} €</div>
                        <div>CO₂-Steuer:</div> <div>${json.details.co2_tax.toFixed(2)} €</div>
                        ${json.details.base_tax > 0 ? `<div>Grundsteuer:</div> <div>${json.details.base_tax.toFixed(2)} €</div>` : ''}
                        ${json.hybrid_discount ? `<div>Hybrid-Rabatt:</div> <div>-${json.hybrid_discount.toFixed(2)} €</div>` : ''}
                    </div>
                    ${json.info ? `<em class="info-note-dynamic">${json.info}</em>` : ''} 
                    
<em class="info-note">Hinweis: Diese Berechnung erfolgt nach den aktuellen Steuersätzen für 2025. Die tatsächliche Steuer kann je nach weiteren Faktoren variieren. Für verbindliche Auskünfte wenden Sie sich an Ihr Finanzamt.</em>
                `;
            }
        })
        .catch(err => {
            resultOut.innerHTML = `<h3>Ihre KFZ-Steuer</h3><strong style="color:red">Serverfehler.</strong>`;
            console.error('Fetch error:', err);
        });
    });

    // Reset form fields and result on page load
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('kfz-tax-form').reset(); // Resets all form fields to their initial (empty) state
        document.getElementById('kfz-tax-result').innerHTML = `
            <h3>Ihre KFZ-Steuer</h3>
            <p style="font-size: 1.2em; color: #666;">Bitte Daten eingeben und berechnen.</p>
    <em class="info-note">Hinweis: Diese Berechnung erfolgt nach den aktuellen Steuersätzen für 2025. Die tatsächliche Steuer kann je nach weiteren Faktoren variieren. Für verbindliche Auskünfte wenden Sie sich an Ihr Finanzamt.</em>
        `;
    });
    </script>
    <?php
    return ob_get_clean();
}
?>