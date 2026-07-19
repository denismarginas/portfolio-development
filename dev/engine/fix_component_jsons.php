<?php

$root = dirname(__DIR__);
$components_dir = $root . '/src/components';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($components_dir, RecursiveDirectoryIterator::SKIP_DOTS)
);

$updated = 0;

foreach ($files as $f) {
    if ($f->getFilename() !== 'component.json') continue;

    $path = $f->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    $dir = basename(dirname($path));

    // Update "name" field to match the directory name
    $content = preg_replace('/"name"\s*:\s*"[^"]+"/', '"name": "' . $dir . '"', $content);

    // Replace any remaining hyphens with underscores in the JSON content
    // (for asset paths, dependencies, etc.)
    $content = str_replace('-', '_', $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "  Updated: " . substr($path, strlen($root) + 1) . "\n";
        $updated++;
    }
}

echo "Total component.json updated: $updated\n";
