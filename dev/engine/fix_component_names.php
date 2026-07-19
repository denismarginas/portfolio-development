<?php

$root = dirname(__DIR__);
$components_dir = $root . '/src/components';
$theme_dir = $root . '/src/theme';

$php_files = [];

$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($components_dir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') $php_files[] = $f->getPathname();
}

$iter2 = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($theme_dir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iter2 as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') $php_files[] = $f->getPathname();
}

$count = 0;
$replacements = 0;

foreach ($php_files as $fp) {
    $content = file_get_contents($fp);
    $orig = $content;

    $content = preg_replace_callback(
        '/(render_component|renderComponent|ComponentRenderer::render|ComponentRenderer::renderComponent)\s*\(\s*[\'"]([a-zA-Z0-9_-]+)[\'"]/',
        function ($m) use ($fp, $root, &$replacements) {
            $func = $m[1];
            $name = $m[2];
            $new_name = str_replace('-', '_', $name);
            if ($new_name !== $name) {
                echo '  ' . substr($fp, strlen($root) + 1) . ': ' . $name . ' -> ' . $new_name . "\n";
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

echo "Files updated: $count, total replacements: $replacements\n";
