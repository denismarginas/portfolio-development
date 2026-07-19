<?php
$root = dirname(__DIR__);
require_once $root . '/engine/bootstrap.php';

$compiler = new ScssCompiler();
$compiler->add_load_path($root . '/src/theme/scss');
$compiler->add_load_path($root . '/src/theme/scss/functions');
$compiler->add_load_path($root . '/src/theme/scss/interface-design');

$entry = $root . '/src/components/section_blog_header/assets/scss/style.scss';
$css = $compiler->compile_file($entry);

echo "Output size: " . strlen($css) . " bytes\n";
echo "Contains \$var: " . (strpos($css, '$var') !== false ? 'YES - BUG!' : 'NO - OK') . "\n";

// Check for key patterns
$checks = ['fix-size', 'fix-width', 'fix-height', '$var', '$vars'];
foreach ($checks as $c) {
    $count = substr_count($css, $c);
    if ($count > 0) echo "  '$c' found $count times\n";
}

// Show lines with $var
$lines = explode("\n", $css);
foreach ($lines as $i => $line) {
    if (str_contains($line, '$var') || str_contains($line, '$vars')) {
        echo "  Line $i: " . trim($line) . "\n";
    }
}
