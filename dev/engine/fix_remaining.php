<?php

$root = dirname(__DIR__);
echo "Fixing remaining hyphens in JSON data files and PHP files...\n";

// ─── Fix JSON files with regex ───

$data_dir = $root . '/src/content/json/data';
$data_files = glob($data_dir . '/*.json');

$fixes = [
    'dir-name'          => 'dir_name',
    'url-path'          => 'url_path',
    'page-text'         => 'page_text',
    'button-text'       => 'button_text',
    'privacy-policy'    => 'privacy_policy',
];

$data_updated = 0;
foreach ($data_files as $fp) {
    $content = file_get_contents($fp);
    $original = $content;

    // Use regex to handle whitespace between key and colon: "key-name" : value
    foreach ($fixes as $hyphen => $underscore) {
        $pattern = '/"' . preg_quote($hyphen, '/') . '"\s*:/';
        $replacement = '"' . $underscore . '":';
        $content = preg_replace($pattern, $replacement, $content);
    }

    if ($content !== $original) {
        file_put_contents($fp, $content);
        echo "  Fixed: " . basename($fp) . "\n";
        $data_updated++;
    }
}

echo "JSON data files fixed: $data_updated\n";

// ─── Fix PHP files with regex (same issue - whitespace in array keys) ───

$components_dir = $root . '/src/components';

$php_files = [];
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($components_dir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iter as $item) {
    if ($item->isFile() && $item->getExtension() === 'php') {
        $php_files[] = $item->getPathname();
    }
}

$php_fixes = [
    'dir-name',
    'url-path',
    'page-text',
    'button-text',
    'privacy-policy',
    'block-1',
    'block-2',
    'block-3',
    'block-4',
];

$php_updated = 0;
foreach ($php_files as $fp) {
    $content = file_get_contents($fp);
    $original = $content;

    foreach ($php_fixes as $key) {
        $underscore_key = str_replace('-', '_', $key);
        $content = preg_replace("/'" . preg_quote($key, '/') . "'/", "'" . $underscore_key . "'", $content);
        $content = preg_replace('/"' . preg_quote($key, '/') . '"/', '"' . $underscore_key . '"', $content);
    }

    if ($content !== $original) {
        file_put_contents($fp, $content);
        echo "  Fixed PHP: " . substr($fp, strlen($root) + 1) . "\n";
        $php_updated++;
    }
}

echo "PHP files fixed: $php_updated\n";
