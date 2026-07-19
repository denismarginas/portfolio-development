<?php

$root = dirname(__DIR__);

// Fix get_data_json calls with hyphenated names
$components_dir = $root . '/src/components';
$php_files = [];

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($components_dir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $php_files[] = $f->getPathname();
    }
}

// Add theme PHP files
$theme_dir = $root . '/src/theme';
$iter2 = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($theme_dir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter2 as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $php_files[] = $f->getPathname();
    }
}

$count = 0;
$replacements = 0;

foreach ($php_files as $fp) {
    $content = file_get_contents($fp);
    $orig = $content;

    $content = preg_replace_callback(
        '/(get_data_json|getDataJson)\s*\(\s*[\'"]([a-zA-Z0-9_-]+)[\'"]/',
        function ($m) use (&$replacements) {
            $func = $m[1];
            $name = $m[2];
            $new_name = str_replace('-', '_', $name);
            if ($new_name !== $name) {
                echo "  $name -> $new_name\n";
                $replacements++;
            }
            return $func . "('" . $new_name . "'";
        },
        $content
    );

    if ($content !== $orig) {
        file_put_contents($fp, $content);
        $count++;
    }
}

echo "Files updated: $count, replacements: $replacements\n";
