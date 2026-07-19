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
$collect_func = $ref->getMethod('collect_function_definitions');
$collect_func->setAccessible(true);
$strip_defs = $ref->getMethod('strip_definitions');
$strip_defs->setAccessible(true);
$replace_vars = $ref->getMethod('replace_variables');
$replace_vars->setAccessible(true);
$expand = $ref->getMethod('expand_includes');
$expand->setAccessible(true);

$scss = file_get_contents($entry);
$scss = $rm_comments->invoke($compiler, $scss);
$scss = $resolve_imports->invoke($compiler, $scss, dirname($entry));
$collect_vars->invoke($compiler, $scss);
$collect_mixin->invoke($compiler, $scss);
$collect_func->invoke($compiler, $scss);

// Preview the text BEFORE stripping
echo "=== Before strip_definitions ===\n";
echo "Contains '@include fix-size': " . (str_contains($scss, '@include fix-size') ? 'YES' : 'NO') . "\n";

$scss = $strip_defs->invoke($compiler, $scss);

echo "=== After strip_definitions ===\n";
echo "Contains '@include fix-size': " . (str_contains($scss, '@include fix-size') ? 'YES' : 'NO') . "\n";
echo "Contains 'fix-size': " . (str_contains($scss, 'fix-size') ? 'YES' : 'NO') . "\n";

// Find the fix-size mixin
$mixins = $ref->getProperty('mixins')->getValue($compiler);
$fix = $mixins['fix-size'] ?? null;
if ($fix) {
    echo "\n=== fix-size mixin ===\n";
    echo "Params: "; print_r($fix['params']);
    echo "Body hex: " . bin2hex($fix['body']) . "\n";
    echo "Body: [" . $fix['body'] . "]\n";
}

$scss = $replace_vars->invoke($compiler, $scss);
echo "\n=== After replace_variables, before expand_includes ===\n";
echo "Contains '@include fix-size': " . (str_contains($scss, '@include fix-size') ? 'YES' : 'NO') . "\n";

// Manually trace expand_includes for one include
echo "\n=== Manual expand of @include fix-size(140px) ===\n";
$mixin = $mixins['fix-size'];
$args = $compiler->parse_mixin_args('140px');
echo "Args: "; print_r($args);
$body = $mixin['body'];
$params = $mixin['params'];
$local_vars = [];
foreach ($params as $idx => $p) {
    if (isset($args[$idx])) {
        $local_vars[$p['name']] = $args[$idx];
    }
}
echo "Local vars: "; print_r($local_vars);
foreach ($local_vars as $name => $value) {
    $regex = '/\B\$' . preg_quote($name) . '\b/';
    echo "Regex: $regex, value: $value\n";
    $body = preg_replace($regex, $value, $body);
    echo "Body after: [" . $body . "]\n";
}
echo "Final body: [" . $body . "]\n";
echo "Contains \$var: " . (str_contains($body, '$var') ? 'YES' : 'NO') . "\n";
