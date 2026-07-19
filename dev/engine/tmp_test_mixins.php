<?php
$root = dirname(__DIR__);
require_once $root . '/engine/bootstrap.php';

$compiler = new ScssCompiler();
$compiler->add_load_path($root . '/src/theme/scss');
$compiler->add_load_path($root . '/src/theme/scss/functions');
$compiler->add_load_path($root . '/src/theme/scss/interface-design');

$entry = $root . '/src/components/section_blog_header/assets/scss/style.scss';

$ref = new ReflectionClass($compiler);

$rm_comments = $ref->getMethod('remove_comments');
$rm_comments->setAccessible(true);
$resolve_imports = $ref->getMethod('resolve_imports');
$resolve_imports->setAccessible(true);
$collect_vars = $ref->getMethod('collect_variables');
$collect_vars->setAccessible(true);
$collect_mixin = $ref->getMethod('collect_mixin_definitions');
$collect_mixin->setAccessible(true);

$scss = file_get_contents($entry);
echo "1. Original: " . strlen($scss) . " bytes\n";

$scss = $rm_comments->invoke($compiler, $scss);
echo "2. After remove_comments: " . strlen($scss) . "\n";

$scss = $resolve_imports->invoke($compiler, $scss, dirname($entry));
echo "3. After resolve_imports: " . strlen($scss) . "\n";

echo "   Contains 'fix-size': " . (str_contains($scss, 'fix-size') ? 'YES' : 'NO') . "\n";
echo "   Contains '@mixin fix-size': " . (str_contains($scss, '@mixin fix-size') ? 'YES' : 'NO') . "\n";

$collect_vars->invoke($compiler, $scss);
$collect_mixin->invoke($compiler, $scss);

echo "4. Variables: " . count($ref->getProperty('variables')->getValue($compiler)) . "\n";
echo "5. Mixins: " . count($ref->getProperty('mixins')->getValue($compiler)) . "\n";

$mixins = $ref->getProperty('mixins')->getValue($compiler);
echo "   Mixin names: " . implode(', ', array_keys($mixins)) . "\n";
echo "   Has fix-size: " . (isset($mixins['fix-size']) ? 'YES' : 'NO') . "\n";
if (isset($mixins['fix-size'])) {
    echo "   fix-size body: [" . $mixins['fix-size']['body'] . "]\n";
}
