<?php
// Sicherheitscheck: Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><span class="dashicons dashicons-book-alt"></span> KFZ Tax Calculator - Dokumentation</h1>
    
    <div class="kfz-docs-container">
        <?php if ($readme_content): ?>
            <div class="kfz-readme-content">
                <?php echo kfz_markdown_to_html($readme_content); ?>
            </div>
        <?php else: ?>
            <div class="notice notice-warning">
                <p>README.md Datei nicht gefunden.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .kfz-docs-container {
        margin-top: 20px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        padding: 30px;
        max-width: none;
    }
    
    .kfz-readme-content {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        line-height: 1.6;
        color: #333;
    }
    
    .kfz-readme-content h1 {
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
        color: #0073aa;
    }
    
    .kfz-readme-content h2 {
        color: #0073aa;
        margin-top: 30px;
        margin-bottom: 15px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    
    .kfz-readme-content h3 {
        color: #333;
        margin-top: 25px;
        margin-bottom: 10px;
    }
    
    .kfz-readme-content code {
        background: #f1f1f1;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: "Courier New", monospace;
        font-size: 90%;
    }
    
    .kfz-readme-content pre {
        background: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        overflow-x: auto;
        margin: 15px 0;
    }
    
    .kfz-readme-content pre code {
        background: none;
        padding: 0;
    }
    
    .kfz-readme-content ul, .kfz-readme-content ol {
        margin: 15px 0;
        padding-left: 30px;
    }
    
    .kfz-readme-content li {
        margin: 5px 0;
    }
    
    .kfz-readme-content table {
        border-collapse: collapse;
        width: 100%;
        margin: 15px 0;
    }
    
    .kfz-readme-content table th,
    .kfz-readme-content table td {
        border: 1px solid #ddd;
        padding: 8px 12px;
        text-align: left;
    }
    
    .kfz-readme-content table th {
        background: #f5f5f5;
        font-weight: bold;
    }
    
    .kfz-readme-content blockquote {
        border-left: 4px solid #0073aa;
        margin: 15px 0;
        padding: 10px 20px;
        background: #f9f9f9;
    }
    
    .kfz-readme-content .emoji {
        font-size: 1.2em;
    }
</style>