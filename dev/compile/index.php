<?php
require_once __DIR__ . '/../engine/bootstrap.php';
require_once __DIR__ . '/scss/compile.php';
require_once __DIR__ . '/js/compile.php';

header('Content-Type: text/html; charset=utf-8');

$action = $_GET['action'] ?? '';

if ($action === 'compile_all') {
    echo '<h1>Compiling All</h1><pre>';
    echo "<strong>SCSS:</strong>\n";
    print_r(ScssCompileManager::compile());
    echo "\n<strong>JS:</strong>\n";
    print_r(JsCompileManager::compile());
    echo '</pre><p><a href="?">Back</a></p>';
    exit;
}

if ($action === 'compile_scss') {
    $filter = $_GET['filter'] ?? null;
    echo '<h1>SCSS Compilation</h1><pre>';
    print_r(ScssCompileManager::compile($filter ?: null));
    echo '</pre><p><a href="?">Back</a></p>';
    exit;
}

if ($action === 'compile_js') {
    $filter = $_GET['filter'] ?? null;
    echo '<h1>JS Compilation</h1><pre>';
    print_r(JsCompileManager::compile($filter ?: null));
    echo '</pre><p><a href="?">Back</a></p>';
    exit;
}

// Display main UI
$scss_config = ScssCompileManager::load_config();
$js_config = JsCompileManager::load_config();

echo '<!DOCTYPE html><html><head><title>Compile Manager</title>';
echo '<style>body{font-family:system-ui,sans-serif;max-width:800px;margin:2em auto;padding:0 1em}';
echo 'h1{color:#333}h2{margin-top:2em}.btn{display:inline-block;padding:8px 16px;border-radius:4px;';
echo 'color:#fff;text-decoration:none;margin:4px}.btn-primary{background:#0066cc}.btn-success{background:#28a745}';
echo '.btn-secondary{background:#6c757d}.btn-group{display:flex;flex-wrap:wrap;gap:8px;align-items:center}';
echo 'pre{background:#f4f4f4;padding:1em;border-radius:4px;overflow-x:auto}</style></head><body>';
echo '<h1>Compile Manager</h1>';

echo '<h2>SCSS Compile</h2>';
echo '<div class="btn-group">';
echo '<a href="?action=compile_all" class="btn btn-success">Compile All SCSS + JS</a>';
echo '<a href="?action=compile_scss" class="btn btn-primary">Compile All SCSS</a>';
if (!empty($scss_config['entries'])) {
    foreach ($scss_config['entries'] as $entry) {
        if (empty($entry['enabled'])) continue;
        $key = $entry['key'] ?? '';
        $label = $entry['label'] ?? $key;
        echo '<a href="?action=compile_scss&filter=' . urlencode($key) . '" class="btn btn-secondary">' . htmlspecialchars($label) . '</a>';
    }
}
echo '</div>';

echo '<h2>JS Compile</h2>';
echo '<div class="btn-group">';
echo '<a href="?action=compile_js" class="btn btn-primary">Compile All JS</a>';
if (!empty($js_config['entries'])) {
    foreach ($js_config['entries'] as $entry) {
        if (empty($entry['enabled'])) continue;
        $key = $entry['key'] ?? '';
        $label = $entry['label'] ?? $key;
        echo '<a href="?action=compile_js&filter=' . urlencode($key) . '" class="btn btn-secondary">' . htmlspecialchars($label) . '</a>';
    }
}
echo '</div>';

echo '</body></html>';