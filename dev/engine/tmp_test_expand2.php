<?php
$root = dirname(__DIR__);
require_once $root . '/engine/bootstrap.php';

$compiler = new ScssCompiler();
$compiler->add_load_path($root . '/src/theme/scss');
$compiler->add_load_path($root . '/src/theme/scss/functions');
$compiler->add_load_path($root . '/src/theme/scss/interface-design');

// Test section_blog_header which uses @include fix-size(140px)
$entry = $root . '/src/components/section_blog_header/assets/scss/style.scss';
$css = $compiler->compile_file($entry);

echo "Has fix-size mixin: " . (isset($compiler->mixins['fix-size']) ? 'YES' : 'NO') . "\n";
if (isset($compiler->mixins['fix-size'])) {
    echo "Mixin body:\n[" . $compiler->mixins['fix-size']['body'] . "]\n";
    echo "Params:\n"; print_r($compiler->mixins['fix-size']['params']);
}

echo "\nContains \$var: " . (strpos($css, '$var') !== false ? 'YES - BUG!' : 'NO - OK') . "\n";
echo "\nCSS (first 600 chars):\n" . substr($css, 0, 600) . "\n";
