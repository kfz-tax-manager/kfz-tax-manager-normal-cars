<?php
// Sicherheitscheck: Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><span class="dashicons dashicons-calculator"></span> KFZ Tax Calculator - Übersicht</h1>
    
    <div class="kfz-admin-dashboard">
        <div class="kfz-stats-grid">
            <div class="kfz-stat-card">
                <h3>Gesamte Anfragen</h3>
                <div class="kfz-stat-number"><?php echo number_format($total_requests); ?></div>
                <p>Seit Plugin-Installation</p>
            </div>
            
            <div class="kfz-stat-card">
                <h3>Heute</h3>
                <div class="kfz-stat-number"><?php echo number_format($today_requests); ?></div>
                <p>Anfragen heute</p>
            </div>
            
            <div class="kfz-stat-card">
                <h3>Plugin Version</h3>
                <div class="kfz-stat-number">1.3</div>
                <p>Aktuelle Version</p>
            </div>
            
            <div class="kfz-stat-card">
                <h3>Status</h3>
                <div class="kfz-stat-number" style="color: #46b450;">✓</div>
                <p>Plugin aktiv</p>
            </div>
        </div>
        
        <div class="kfz-recent-section">
            <h2>Letzte Berechnungen</h2>
            <?php if (!empty($recent_requests)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Zeitpunkt</th>
                            <th>Fahrzeugtyp</th>
                            <th>Hubraum</th>
                            <th>CO2</th>
                            <th>Jahr</th>
                            <th>Steuer/Jahr</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_requests as $request): ?>
                            <tr>
                                <td><?php echo date('d.m.Y H:i', strtotime($request['timestamp'])); ?></td>
                                <td>
                                    <?php 
                                    $type_icons = [
                                        'benzin' => '⛽',
                                        'diesel' => '🚗',
                                        'hybrid' => '🌱',
                                        'elektro' => '⚡'
                                    ];
                                    echo $type_icons[$request['input']['type']] ?? '';
                                    echo ' ' . ucfirst($request['input']['type']);
                                    ?>
                                </td>
                                <td><?php echo number_format($request['input']['displacement']); ?> ccm</td>
                                <td><?php echo $request['input']['co2_emission']; ?> g/km</td>
                                <td><?php echo $request['input']['first_registration_year']; ?></td>
                                <td><strong><?php echo number_format($request['result']['annual'], 2); ?> €</strong></td>
                                <td><?php echo substr($request['ip'], 0, -2) . 'xx'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><a href="<?php echo admin_url('admin.php?page=kfz-tax-calculator-logs'); ?>" class="button">Alle Anfragen anzeigen</a></p>
            <?php else: ?>
                <p>Noch keine Berechnungen durchgeführt.</p>
            <?php endif; ?>
        </div>
        
        <div class="kfz-info-section">
            <h2>Plugin Information</h2>
            <div class="kfz-info-grid">
                <div class="kfz-info-card">
                    <h4>Shortcode</h4>
                    <code>[kfz_tax_form]</code>
                    <p>Fügen Sie diesen Shortcode in Seiten oder Beiträge ein, um das Steuerformular anzuzeigen.</p>
                </div>
                
                <div class="kfz-info-card">
                    <h4>Unterstützte Fahrzeugtypen</h4>
                    <ul>
                        <li>⛽ Benziner PKW</li>
                        <li>🚗 Diesel PKW</li>
                        <li>🌱 Hybrid PKW</li>
                        <li>⚡ Elektro PKW</li>
                    </ul>
                </div>
                
                <div class="kfz-info-card">
                    <h4>Steuerberechnung 2025</h4>
                    <p><strong>Benziner:</strong> 2,00€/100ccm + CO2-Steuer ab 95 g/km</p>
                    <p><strong>Diesel:</strong> 9,50€/100ccm + CO2-Steuer ab 95 g/km</p>
                    <p><strong>Hybrid:</strong> Wie Benziner (keine Steuerbefreiung)</p>
                    <p><strong>Elektro:</strong> Steuerbefreit bis 31.12.2030 (Erstzulassung 2021-2025)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .kfz-admin-dashboard {
        margin-top: 20px;
    }
    
    .kfz-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .kfz-stat-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    
    .kfz-stat-card h3 {
        margin: 0 0 10px 0;
        font-size: 14px;
        color: #666;
    }
    
    .kfz-stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #0073aa;
        margin: 10px 0;
    }
    
    .kfz-stat-card p {
        margin: 0;
        color: #666;
        font-size: 12px;
    }
    
    .kfz-recent-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .kfz-recent-section h2 {
        margin-top: 0;
    }
    
    .kfz-info-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
    }
    
    .kfz-info-section h2 {
        margin-top: 0;
    }
    
    .kfz-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .kfz-info-card {
        border: 1px solid #e1e1e1;
        border-radius: 4px;
        padding: 15px;
        background: #f9f9f9;
    }
    
    .kfz-info-card h4 {
        margin: 0 0 10px 0;
        color: #0073aa;
    }
    
    .kfz-info-card code {
        background: #f1f1f1;
        padding: 4px 8px;
        border-radius: 3px;
        font-family: monospace;
    }
    
    .kfz-info-card ul {
        margin: 10px 0;
        padding-left: 20px;
    }
</style>