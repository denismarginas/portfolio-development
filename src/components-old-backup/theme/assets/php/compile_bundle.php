<?php

$rootDir = dirname(__DIR__, 5);
$componentsDir = $rootDir . '/src/components';
$themeDir = $componentsDir . '/theme';
$compiledDir = $themeDir . '/assets_compiled';

$componentDirs = glob($componentsDir . '/*', GLOB_ONLYDIR);
$componentNames = array_map('basename', $componentDirs);
sort($componentNames);

$others = array_values(array_filter($componentNames, fn($n) => $n !== 'theme'));
$order = array_merge(['theme'], $others);

$cssContent = '';
$jsContent = '';

foreach ($order as $name) {
    $configPath = $componentsDir . '/' . $name . '/component.json';
    if (!file_exists($configPath)) continue;
    $config = json_decode(file_get_contents($configPath), true);
    if (!$config) continue;

    $assets = $config['assets'] ?? [];

    foreach ($assets['css'] ?? [] as $file) {
        if (str_ends_with($file, '.scss')) continue;
        $fullPath = $componentsDir . '/' . $name . '/' . $file;
        if (file_exists($fullPath)) {
            $cssContent .= file_get_contents($fullPath) . "\n";
        }
    }

    foreach ($assets['js'] ?? [] as $file) {
        $fullPath = $componentsDir . '/' . $name . '/' . $file;
        if (file_exists($fullPath)) {
            $jsContent .= file_get_contents($fullPath) . "\n";
        }
    }
}

$cssDir = $compiledDir . '/css';
$jsDir = $compiledDir . '/js';
if (!is_dir($cssDir)) mkdir($cssDir, 0755, true);
if (!is_dir($jsDir)) mkdir($jsDir, 0755, true);

file_put_contents($cssDir . '/bundle.css', $cssContent);
file_put_contents($jsDir . '/bundle.js', $jsContent);

echo "Bundle compiled:\n";
echo "  CSS: " . $cssDir . "/bundle.css (" . strlen($cssContent) . " bytes, " . count(explode("\n", $cssContent)) . " lines)\n";
echo "  JS:  " . $jsDir . "/bundle.js (" . strlen($jsContent) . " bytes, " . count(explode("\n", $jsContent)) . " lines)\n";
