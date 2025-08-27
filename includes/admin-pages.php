<?php
/**
 * Admin-Seiten für das KFZ Tax Calculator Plugin
 */

// Sicherheitscheck: Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fügt das Admin-Menü hinzu
 */
function kfz_tax_calculator_admin_menu() {
    add_menu_page(
        'KFZ Tax Calculator',
        'KFZ Steuer',
        'manage_options',
        'kfz-tax-calculator',
        'kfz_tax_calculator_admin_page',
        'dashicons-calculator',
        30
    );
    
    add_submenu_page(
        'kfz-tax-calculator',
        'Übersicht',
        'Übersicht',
        'manage_options',
        'kfz-tax-calculator',
        'kfz_tax_calculator_admin_page'
    );
    
    add_submenu_page(
        'kfz-tax-calculator',
        'Anfragen & Logs',
        'Anfragen & Logs',
        'manage_options',
        'kfz-tax-calculator-logs',
        'kfz_tax_calculator_logs_page'
    );
    
    add_submenu_page(
        'kfz-tax-calculator',
        'Dokumentation',
        'Dokumentation',
        'manage_options',
        'kfz-tax-calculator-docs',
        'kfz_tax_calculator_docs_page'
    );
}

/**
 * Hauptseite des Admin-Bereichs
 */
function kfz_tax_calculator_admin_page() {
    $log_file = plugin_dir_path(dirname(__FILE__)) . 'requests.log';
    $total_requests = 0;
    $today_requests = 0;
    $recent_requests = [];
    
    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $total_requests = count($lines);
        
        $today = date('Y-m-d');
        foreach (array_reverse($lines) as $line) {
            $data = json_decode($line, true);
            if ($data && isset($data['timestamp'])) {
                $request_date = date('Y-m-d', strtotime($data['timestamp']));
                if ($request_date === $today) {
                    $today_requests++;
                }
                
                if (count($recent_requests) < 5) {
                    $recent_requests[] = $data;
                }
            }
        }
    }
    
    include plugin_dir_path(__FILE__) . 'admin-templates/overview.php';
}

/**
 * Logs und Anfragen Seite
 */
function kfz_tax_calculator_logs_page() {
    $log_file = plugin_dir_path(dirname(__FILE__)) . 'requests.log';
    $requests = [];
    $stats = [
        'total' => 0,
        'today' => 0,
        'this_week' => 0,
        'this_month' => 0,
        'by_type' => ['benzin' => 0, 'diesel' => 0, 'hybrid' => 0, 'elektro' => 0]
    ];
    
    // Paginierung
    $per_page = 50;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    
    // Log-Datei lesen und analysieren
    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $stats['total'] = count($lines);
        
        $today = date('Y-m-d');
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $month_start = date('Y-m-01');
        
        foreach (array_reverse($lines) as $line) {
            $data = json_decode($line, true);
            if ($data && isset($data['timestamp'])) {
                $request_date = date('Y-m-d', strtotime($data['timestamp']));
                
                // Statistiken berechnen
                if ($request_date === $today) $stats['today']++;
                if ($request_date >= $week_start) $stats['this_week']++;
                if ($request_date >= $month_start) $stats['this_month']++;
                
                if (isset($data['input']['type'])) {
                    $stats['by_type'][$data['input']['type']]++;
                }
                
                $requests[] = $data;
            }
        }
        
        // Paginierung anwenden
        $total_requests = count($requests);
        $requests = array_slice($requests, $offset, $per_page);
        $total_pages = ceil($total_requests / $per_page);
    }
    
    // Log löschen verarbeiten
    if (isset($_POST['clear_logs']) && wp_verify_nonce($_POST['_wpnonce'], 'clear_logs')) {
        if (file_exists($log_file)) {
            unlink($log_file);
            echo '<div class="notice notice-success"><p>Logs wurden erfolgreich gelöscht.</p></div>';
            $requests = [];
            $stats = ['total' => 0, 'today' => 0, 'this_week' => 0, 'this_month' => 0, 'by_type' => ['benzin' => 0, 'diesel' => 0, 'hybrid' => 0, 'elektro' => 0]];
        }
    }
    
    include plugin_dir_path(__FILE__) . 'admin-templates/logs.php';
}

/**
 * Dokumentations-Seite
 */
function kfz_tax_calculator_docs_page() {
    $readme_file = plugin_dir_path(dirname(__FILE__)) . 'README.md';
    $readme_content = '';
    
    if (file_exists($readme_file)) {
        $readme_content = file_get_contents($readme_file);
    }
    
    include plugin_dir_path(__FILE__) . 'admin-templates/documentation.php';
}

/**
 * Einfacher Markdown zu HTML Konverter
 */
function kfz_markdown_to_html($markdown) {
    // Basis-Markdown-Konvertierung
    $html = $markdown;
    
    // Headers
    $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
    
    // Bold und Italic
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
    
    // Code blocks
    $html = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $html);
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
    
    // Links
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $html);
    
    // Listen
    $html = preg_replace('/^- (.*$)/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $html);
    
    // Nummerierte Listen
    $html = preg_replace('/^\d+\. (.*$)/m', '<li>$1</li>', $html);
    
    // Paragraphen
    $html = preg_replace('/\n\n/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';
    
    // Zeilenumbrüche
    $html = str_replace("\n", '<br>', $html);
    
    // Emojis als CSS-Klasse markieren
    $html = preg_replace('/([\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}])/u', '<span class="emoji">$1</span>', $html);
    
    return $html;
}