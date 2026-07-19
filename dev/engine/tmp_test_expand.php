<?php
$root = dirname(__DIR__);
require_once $root . '/engine/bootstrap.php';

$compiler = new ScssCompiler();
$compiler->add_load_path($root . '/src/theme/scss');
$compiler->add_load_path($root . '/src/theme/scss/functions');
$compiler->add_load_path($root . '/src/theme/scss/interface-design');

// Test a specific component that uses fix-size
$entry = $root . '/src/components/search_post_button/assets/scss/style.scss';
$css = $compiler->compile_file($entry);

echo "Has fix-size mixin: " . (isset($compiler->mixins['fix-size']) ? 'YES' : 'NO') . "\n";
if (isset($compiler->mixins['fix-size'])) {
    echo "Mixin body:\n[" . $compiler->mixins['fix-size']['body'] . "]\n";
    echo "Params:\n"; print_r($compiler->mixins['fix-size']['params']);
}

echo "\nCSS output (first 500 chars):\n" . substr($css, 0, 500) . "\n";
echo "\nContains \$var: " . (strpos($css, '$var') !== false ? 'YES - BUG!' : 'NO - OK') . "\n";
