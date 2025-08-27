<?php
// Sicherheitscheck: Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><span class="dashicons dashicons-list-view"></span> KFZ Tax Calculator - Anfragen & Logs</h1>
    
    <div class="kfz-logs-dashboard">
        <!-- Statistiken -->
        <div class="kfz-stats-grid">
            <div class="kfz-stat-card">
                <h3>Gesamt</h3>
                <div class="kfz-stat-number"><?php echo number_format($stats['total']); ?></div>
                <p>Alle Anfragen</p>
            </div>
            
            <div class="kfz-stat-card">
                <h3>Heute</h3>
                <div class="kfz-stat-number"><?php echo number_format($stats['today']); ?></div>
                <p>Anfragen heute</p>
            </div>
            
            <div class="kfz-stat-card">
                <h3>Diese Woche</h3>
                <div class="kfz-stat-number"><?php echo number_format($stats['this_week']); ?></div>
                <p>Anfragen diese Woche</p>
            </div>
            
            <div class="kfz-stat-card">
                <h3>Dieser Monat</h3>
                <div class="kfz-stat-number"><?php echo number_format($stats['this_month']); ?></div>
                <p>Anfragen diesen Monat</p>
            </div>
        </div>
        
        <!-- Fahrzeugtyp-Statistiken -->
        <div class="kfz-type-stats">
            <h2>Anfragen nach Fahrzeugtyp</h2>
            <div class="kfz-type-grid">
                <div class="kfz-type-card">
                    <span class="kfz-type-icon">⛽</span>
                    <div class="kfz-type-info">
                        <strong><?php echo number_format($stats['by_type']['benzin']); ?></strong>
                        <span>Benziner</span>
                    </div>
                </div>
                <div class="kfz-type-card">
                    <span class="kfz-type-icon">🚗</span>
                    <div class="kfz-type-info">
                        <strong><?php echo number_format($stats['by_type']['diesel']); ?></strong>
                        <span>Diesel</span>
                    </div>
                </div>
                <div class="kfz-type-card">
                    <span class="kfz-type-icon">🌱</span>
                    <div class="kfz-type-info">
                        <strong><?php echo number_format($stats['by_type']['hybrid']); ?></strong>
                        <span>Hybrid</span>
                    </div>
                </div>
                <div class="kfz-type-card">
                    <span class="kfz-type-icon">⚡</span>
                    <div class="kfz-type-info">
                        <strong><?php echo number_format($stats['by_type']['elektro']); ?></strong>
                        <span>Elektro</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Aktionen -->
        <div class="kfz-actions">
            <form method="post" style="display: inline;">
                <?php wp_nonce_field('clear_logs'); ?>
                <input type="submit" name="clear_logs" class="button button-secondary" value="Logs löschen" 
                       onclick="return confirm('Sind Sie sicher, dass Sie alle Logs löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.');">
            </form>
            
            <?php if (file_exists($log_file)): ?>
                <a href="<?php echo plugin_dir_url(dirname(__FILE__)) . 'requests.log'; ?>" class="button button-secondary" target="_blank">Log-Datei herunterladen</a>
            <?php endif; ?>
        </div>
        
        <!-- Anfragen-Tabelle -->
        <div class="kfz-requests-section">
            <h2>Alle Anfragen 
                <?php if ($stats['total'] > 0): ?>
                    <span class="kfz-count">(<?php echo number_format($stats['total']); ?> gesamt)</span>
                <?php endif; ?>
            </h2>
            
            <?php if (!empty($requests)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 140px;">Zeitpunkt</th>
                            <th style="width: 100px;">Fahrzeugtyp</th>
                            <th style="width: 80px;">Hubraum</th>
                            <th style="width: 70px;">CO2</th>
                            <th style="width: 60px;">Jahr</th>
                            <th style="width: 70px;">Gewicht</th>
                            <th style="width: 100px;">Steuer/Jahr</th>
                            <th style="width: 80px;">Monatlich</th>
                            <th style="width: 120px;">IP-Adresse</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo date('d.m.Y H:i:s', strtotime($request['timestamp'])); ?></td>
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
                                <td><?php echo isset($request['input']['weight']) && $request['input']['weight'] > 0 ? number_format($request['input']['weight']) . ' kg' : '-'; ?></td>
                                <td><strong><?php echo number_format($request['result']['annual'], 2); ?> €</strong></td>
                                <td><?php echo number_format($request['result']['monthly'], 2); ?> €</td>
                                <td><code><?php echo substr($request['ip'], 0, -2) . 'xx'; ?></code></td>
                                <td style="font-size: 11px; color: #666;">
                                    <?php echo esc_html(substr($request['user_agent'] ?? 'Unbekannt', 0, 60)); ?>
                                    <?php if (strlen($request['user_agent'] ?? '') > 60): ?>...<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Paginierung -->
                <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo number_format($total_requests); ?> Einträge</span>
                            <?php
                            $page_links = paginate_links([
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page
                            ]);
                            echo $page_links;
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <p>Noch keine Anfragen vorhanden.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .kfz-logs-dashboard {
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
    
    .kfz-type-stats {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .kfz-type-stats h2 {
        margin-top: 0;
    }
    
    .kfz-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }
    
    .kfz-type-card {
        display: flex;
        align-items: center;
        padding: 15px;
        border: 1px solid #e1e1e1;
        border-radius: 4px;
        background: #f9f9f9;
    }
    
    .kfz-type-icon {
        font-size: 24px;
        margin-right: 10px;
    }
    
    .kfz-type-info strong {
        display: block;
        font-size: 18px;
        color: #0073aa;
    }
    
    .kfz-type-info span {
        font-size: 12px;
        color: #666;
    }
    
    .kfz-actions {
        margin-bottom: 20px;
    }
    
    .kfz-actions .button {
        margin-right: 10px;
    }
    
    .kfz-requests-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 20px;
    }
    
    .kfz-requests-section h2 {
        margin-top: 0;
    }
    
    .kfz-count {
        font-size: 14px;
        font-weight: normal;
        color: #666;
    }
    
    .wp-list-table th,
    .wp-list-table td {
        padding: 8px 10px;
    }
    
    .tablenav {
        margin-top: 20px;
    }
</style>