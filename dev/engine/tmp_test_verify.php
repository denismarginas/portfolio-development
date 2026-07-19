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
$lines = explode("\n", $css);
$var_lines = [];
foreach ($lines as $i => $line) {
    if (str_contains($line, '$var') || str_contains($line, '$vars')) {
        $var_lines[] = "  Line $i: " . trim($line);
    }
}
if ($var_lines) {
    echo "\$var lines:\n" . implode("\n", $var_lines) . "\n";
} else {
    echo "No \$var found — passing!\n";
    // Show fix-size mentions
    foreach ($lines as $i => $line) {
        $t = trim($line);
        if (str_contains($t, 'width') || str_contains($t, 'height')) {
            if (preg_match('/^\d/', $t[0] ?? '')) continue;
            echo "  Line $i: $t\n";
        }
    }
}
