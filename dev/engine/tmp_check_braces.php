<?php
require_once __DIR__ . '/bootstrap.php';

$css = file_get_contents(EngineConfig::get('theme_css_dir') . '/style.css');
$lines = explode("\n", $css);
$found = false;
foreach ($lines as $i => $l) {
    if (preg_match('/\}\s*\}$/', $l)) {
        echo "Line " . ($i + 1) . ": " . $l . "\n";
        $found = true;
    }
}
if (!$found) echo "No double-closing braces found.\n";
echo "Total lines: " . count($lines) . "\n";