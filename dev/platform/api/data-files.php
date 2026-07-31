<?php

header('Content-Type: application/json; charset=UTF-8');

$dataDir = __DIR__ . '/../../../src/content/json/data';

if (!is_dir($dataDir)) {
    echo json_encode(['files' => []]);
    exit;
}

$files = [];

foreach (new DirectoryIterator($dataDir) as $item) {
    if ($item->isFile() && strtolower($item->getExtension()) === 'json') {
        $files[] = $item->getFilename();
    }
}

sort($files);

echo json_encode(['files' => $files], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
