<?php

header('Content-Type: application/json; charset=UTF-8');

$baseDir = dirname(__DIR__, 3);
$componentsDir = $baseDir . '/src/components';
$results = [];
$hasErrors = false;

if (!is_dir($componentsDir)) {
    echo json_encode(['error' => 'Components directory not found']);
    exit;
}

foreach (new DirectoryIterator($componentsDir) as $comp) {
    if (!$comp->isDir() || $comp->isDot()) continue;

    $scssFile = $comp->getPathname() . '/assets/scss/style.scss';
    $cssDir = $comp->getPathname() . '/assets/css';

    if (!file_exists($scssFile)) continue;

    if (!is_dir($cssDir)) {
        mkdir($cssDir, 0777, true);
    }

    $cssFile = $cssDir . '/style.css';
    $compName = $comp->getFilename();

    $loadPath = $baseDir . '/src/components/theme/assets/scss';
    $cmd = sprintf(
        'sass "%s" "%s" --style=compressed --load-path="%s" --no-source-map --silence-deprecation=import 2>&1',
        $scssFile,
        $cssFile,
        $loadPath
    );

    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);

    $results[] = [
        'component' => $compName,
        'input' => 'src/components/' . $compName . '/assets/scss/style.scss',
        'output' => 'src/components/' . $compName . '/assets/css/style.css',
        'success' => $exitCode === 0,
        'message' => $exitCode === 0 ? 'Compiled' : trim(implode("\n", $output)),
    ];

    if ($exitCode !== 0) {
        $hasErrors = true;
    }
}

echo json_encode([
    'total' => count($results),
    'success_count' => count(array_filter($results, fn($r) => $r['success'])),
    'error_count' => count(array_filter($results, fn($r) => !$r['success'])),
    'results' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
