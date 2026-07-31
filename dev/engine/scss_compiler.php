<?php

class scss_compiler
{
    private array $variables = [];
    private array $mixins = [];
    private array $functions = [];
    private array $load_paths = [];
    private array $imported_files = [];
    private string $root_dir;

    private const INTERPOLATION_REGEX = '/#\{([^}]*)\}/';
    private const VARIABLE_REGEX = '/\B\$([a-zA-Z_][a-zA-Z0-9_-]*)\b/';
    private const MIXIN_DEF_REGEX = '/@mixin\s+([a-zA-Z_][a-zA-Z0-9_-]*)\s*(\([^)]*\))?\s*\{/';
    private const FUNCTION_DEF_REGEX = '/@function\s+([a-zA-Z_][a-zA-Z0-9_-]*)\s*\(([^)]*)\)\s*\{/';
    private const INCLUDE_REGEX = '/@include\s+([a-zA-Z_][a-zA-Z0-9_-]*)\s*(\([^)]*\))?\s*;/';

    public function __construct()
    {
        $this->root_dir = dirname(__DIR__);
    }

    public function add_load_path(string $path): void
    {
        if (is_dir($path)) {
            $this->load_paths[] = $path;
        }
    }

    public function compile(string $scss, ?string $source_file = null): string
    {
        $this->variables = [];
        $this->mixins = [];
        $this->functions = [];
        $this->imported_files = [];

        $working_dir = $source_file ? dirname($source_file) : $this->root_dir;

        // 1. Preprocess
        $scss = $this->remove_comments($scss);

        // 2. Resolve imports
        $scss = $this->resolve_imports($scss, $working_dir);

        // 3-5. Collect definitions and resolve (repeat until stable for nested imports)
        $scss = $this->resolve_all($scss);

        // 6. Flatten nesting
        $css = $this->flatten_nesting($scss);

        // 7. Clean up
        $css = $this->clean_output($css);

        return $css;
    }

    private function resolve_all(string $scss): string
    {
        // Collect variables at root level
        $this->collect_variables($scss);

        // Collect mixin definitions
        $this->collect_mixin_definitions($scss);

        // Collect function definitions
        $this->collect_function_definitions($scss);

        // Strip definitions from output
        $scss = $this->strip_definitions($scss);

        // Replace variables
        $scss = $this->replace_variables($scss);

        // Expand includes
        $scss = $this->expand_includes($scss);

        // Evaluate functions (before interpolation, so #{} can wrap function calls)
        $scss = $this->evaluate_functions($scss);

        // Replace interpolation (must run after functions for #{func()} cases)
        $scss = $this->replace_interpolation($scss);

        return $scss;
    }

    private function remove_comments(string $scss): string
    {
        // Remove multi-line comments
        $scss = preg_replace('/\/\*[\s\S]*?\*\//', '', $scss);
        // Remove single-line comments (but not inside strings)
        $lines = explode("\n", $scss);
        $result = [];
        foreach ($lines as $line) {
            // Simple approach: remove // comments, but be careful with urls
            $line = preg_replace('/\/\/.*$/', '', $line);
            $result[] = $line;
        }
        return implode("\n", $result);
    }

    private function resolve_imports(string $scss, string $working_dir): string
    {
        $pattern = '/@import\s+["\']([^"\']+)["\']\s*;/';
        return preg_replace_callback($pattern, function ($matches) use ($working_dir) {
            $import_path = $matches[1];

            // Don't process CSS imports
            if (str_ends_with($import_path, '.css')) {
                return $matches[0];
            }

            // Check if importing a directory (no file extension)
            if (!str_ends_with($import_path, '.scss')) {
                $dir = $this->find_import_dir($import_path, $working_dir);
                if ($dir !== null) {
                    return $this->import_directory($dir);
                }
            }

            // Single file import
            $path = $this->find_import_file($import_path, $working_dir);
            if ($path === null) {
                return '/* Import not found: ' . $import_path . ' */';
            }
            return $this->import_file($path);
        }, $scss);
    }

    private function find_import_dir(string $import_path, string $working_dir): ?string
    {
        $search_dirs = array_unique([
            $working_dir,
            dirname($working_dir),
            $this->root_dir,
        ]);
        $search_dirs = array_merge($search_dirs, $this->load_paths);

        foreach ($search_dirs as $dir) {
            $full_path = $this->normalize_path($dir . '/' . ltrim($import_path, '/'));
            if (is_dir($full_path)) {
                return $full_path;
            }
        }
        return null;
    }

    private function import_directory(string $dir): string
    {
        $files = glob($dir . '/*.scss');
        if (empty($files)) {
            return '/* No SCSS files in directory: ' . $dir . ' */';
        }

        sort($files);
        $result = '';
        foreach ($files as $path) {
            $basename = basename($path);
            // Skip partials and entry files
            if (str_starts_with($basename, '_') || $basename === 'style.scss') {
                continue;
            }
            $result .= $this->import_file($path) . "\n";
        }
        return $result;
    }

    private function import_file(string $path): string
    {
        $cache_key = realpath($path);
        if ($cache_key === false) {
            return '/* Import file not found: ' . $path . ' */';
        }
        if (isset($this->imported_files[$cache_key])) {
            return '';
        }
        $this->imported_files[$cache_key] = true;

        $content = file_get_contents($path);
        $content = $this->remove_comments($content);

        $import_dir = dirname($path);

        // Collect variables etc. from the imported file
        $this->collect_variables_from_content($content);
        $this->collect_mixin_definitions_from_content($content);
        $this->collect_function_definitions_from_content($content);

        // Recursively resolve imports in the imported file
        $content = $this->resolve_imports($content, $import_dir);

        return $content;
    }

    private function find_import_file(string $import_path, string $working_dir): ?string
    {
        // Add .scss extension if not present
        if (!str_ends_with($import_path, '.scss') && !str_ends_with($import_path, '.css')) {
            $import_path .= '.scss';
        }

        // If it has _ prefix convention, try both
        $basename = basename($import_path);
        $dirname = dirname($import_path);

        $candidates = [$import_path];
        if (!str_starts_with($basename, '_')) {
            $candidates[] = $dirname . '/_' . $basename;
        }

        // Check paths
        $search_dirs = array_unique([
            $working_dir,
            dirname($working_dir),
            $this->root_dir,
        ]);
        $search_dirs = array_merge($search_dirs, $this->load_paths);

        foreach ($search_dirs as $dir) {
            foreach ($candidates as $candidate) {
                $full_path = $dir . '/' . ltrim($candidate, '/');
                $full_path = $this->normalize_path($full_path);
                if (file_exists($full_path)) {
                    return $full_path;
                }
            }
        }

        return null;
    }

    private function collect_variables(string &$scss): void
    {
        $this->collect_variables_from_content($scss);
    }

    private function collect_variables_from_content(string $content): void
    {
        $pattern = '/^(\s*)\$([a-zA-Z_][a-zA-Z0-9_-]*)\s*:\s*(.+?)\s*;/m';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $var_name = $match[2];
            $var_value = trim($match[3]);

            // Only set if not already defined (or re-defined)
            if (!isset($this->variables[$var_name]) || $this->is_at_root_level($match[1])) {
                // Resolve any variable references within the value
                $var_value = $this->resolve_variable_value($var_value);
                $this->variables[$var_name] = $var_value;
            }
        }
    }

    private function is_at_root_level(string $indent): bool
    {
        return empty(trim($indent));
    }

    private function resolve_variable_value(string $value): string
    {
        return preg_replace_callback(self::VARIABLE_REGEX, function ($m) {
            $name = $m[1];
            return $this->variables[$name] ?? $m[0];
        }, $value);
    }

    private function collect_mixin_definitions(string &$scss): void
    {
        $this->collect_mixin_definitions_from_content($scss);
    }

    private function collect_mixin_definitions_from_content(string $content): void
    {
        // Find all @mixin blocks
        $this->collect_blocks($content, '@mixin', function ($name, $params, $body, $raw) {
            $this->mixins[$name] = [
                'params' => $this->parse_mixin_params($params),
                'body'   => $body,
            ];
        });
    }

    private function collect_function_definitions(string &$scss): void
    {
        $this->collect_function_definitions_from_content($scss);
    }

    private function collect_function_definitions_from_content(string $content): void
    {
        $this->collect_blocks($content, '@function', function ($name, $params, $body, $raw) {
            $this->functions[$name] = [
                'params' => $this->parse_mixin_params($params),
                'body'   => $body,
            ];
        });
    }

    private function collect_blocks(string $content, string $type, callable $callback): void
    {
        $prefix_len = strlen($type) + 1; // "@type " or "@mixin "
        $lines = explode("\n", $content);

        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if (str_starts_with($trimmed, $type)) {
                // Parse the definition line
                $def_content = $trimmed;
                $brace_pos = strpos($trimmed, '{');

                if ($brace_pos !== false) {
                    $def_line = substr($trimmed, 0, $brace_pos);
                    $body_start = $trimmed; // includes the brace
                } else {
                    $def_line = $trimmed;
                    $body_start = '';
                    // Look ahead for opening brace
                    for ($j = $i + 1; $j < count($lines); $j++) {
                        $next = trim($lines[$j]);
                        if (str_contains($next, '{')) {
                            $brace_pos_in_next = strpos($next, '{');
                            if ($brace_pos_in_next !== false) {
                                $def_line .= ' ' . substr($next, 0, $brace_pos_in_next);
                                $body_start = $next;
                                $i = $j;
                                break;
                            }
                        }
                    }
                }

                // Extract name and params
                $after_type = substr($def_line, strlen($type) + 1);
                $paren_pos = strpos($after_type, '(');
                if ($paren_pos !== false) {
                    $name = trim(substr($after_type, 0, $paren_pos));
                    $params_str = substr($after_type, $paren_pos);
                } else {
                    $name = trim($after_type);
                    $params_str = '';
                }

                // Find the full block body
                $body = '';
                $depth = 1;
                $body_started = false;

                // Process the remaining content from the current position
                $search_lines = array_slice($lines, $i);
                $full_block = implode("\n", $search_lines);
                $first_brace = strpos($full_block, '{');

                if ($first_brace !== false) {
                    $after_brace = substr($full_block, $first_brace + 1);
                    $body = '';
                    $depth = 1;
                    $pos = 0;
                    $len = strlen($after_brace);

                    while ($pos < $len && $depth > 0) {
                        $ch = $after_brace[$pos];
                        if ($ch === '{') $depth++;
                        elseif ($ch === '}') $depth--;
                        if ($depth > 0 || ($depth === 0 && $ch === '}')) {
                            // Don't add the closing brace
                            if (!($depth === 0 && $ch === '}')) {
                                $body .= $ch;
                            }
                        }
                        $pos++;
                    }
                }

                if (!empty($name)) {
                    $callback($name, $params_str, $body, $trimmed);
                }
            }
            $i++;
        }
    }

    private function parse_mixin_params(string $params_str): array
    {
        $params = [];
        if (empty($params_str)) return $params;

        $params_str = trim($params_str);
        $params_str = trim($params_str, '()');
        $params_str = trim($params_str);
        if (empty($params_str)) return $params;

        $parts = explode(',', $params_str);
        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            // Check for default value
            if (str_contains($part, ':')) {
                [$name, $default] = explode(':', $part, 2);
                $name = trim($name);
                // Check if it's varargs
                $is_vararg = str_ends_with($name, '...');
                if ($is_vararg) $name = substr($name, 0, -3);
                $params[] = [
                    'name'       => ltrim($name, '$'),
                    'default'    => trim($default),
                    'is_vararg'  => $is_vararg,
                ];
            } else {
                $is_vararg = str_ends_with($part, '...');
                $name = $is_vararg ? substr($part, 0, -3) : $part;
                $params[] = [
                    'name'      => ltrim($name, '$'),
                    'default'   => null,
                    'is_vararg' => $is_vararg,
                ];
            }
        }

        return $params;
    }

    private function strip_definitions(string $scss): string
    {
        // 1. Remove variable definition lines ($var: value;)
        $scss = preg_replace('/^\s*\$[a-zA-Z_][a-zA-Z0-9_-]*\s*:\s*.+?;\s*$/m', '', $scss);

        // 2. Remove @mixin blocks
        $scss = preg_replace_callback('/@mixin\s+([a-zA-Z_][a-zA-Z0-9_-]*)\s*(\([^)]*\))?\s*\{/', function ($m) {
            return '@mixin ' . $m[1] . $m[2] . ' {';
        }, $scss);

        // Use a stack-based approach to remove @mixin and @function blocks
        $result = '';
        $lines = explode("\n", $scss);
        $depth = 0;
        $skip_depth = -1;
        $in_mixin_or_function = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $was_skipping = $in_mixin_or_function;

            if (preg_match('/^@mixin\s+/', $trimmed) || preg_match('/^@function\s+/', $trimmed)) {
                $in_mixin_or_function = true;
                $skip_depth = $depth;
                $skip_depth += substr_count($trimmed, '{') - substr_count($trimmed, '}');
                continue;
            }

            if (!$in_mixin_or_function) {
                $result .= $line . "\n";
            }

            $open_count = substr_count($trimmed, '{');
            $close_count = substr_count($trimmed, '}');

            if (!$was_skipping) {
                $depth += $open_count - $close_count;
            }

            if ($in_mixin_or_function) {
                $skip_depth += $open_count - $close_count;
                if ($skip_depth <= 0 && $close_count > 0) {
                    $in_mixin_or_function = false;
                    $skip_depth = -1;
                }
            }
        }

        return $result;
    }

    private function replace_variables(string $scss): string
    {
        // Replace $variable references
        return preg_replace_callback(self::VARIABLE_REGEX, function ($matches) {
            $name = $matches[1];
            return $this->variables[$name] ?? $matches[0];
        }, $scss);
    }

    private function replace_interpolation(string $scss): string
    {
        return preg_replace_callback(self::INTERPOLATION_REGEX, function ($matches) {
            $inner = trim($matches[1]);
            // If it's a simple variable reference
            if (str_starts_with($inner, '$')) {
                $name = substr($inner, 1);
                return $this->variables[$name] ?? $matches[0];
            }
            // Otherwise, resolve any variable references within the expression
            $inner = preg_replace_callback('/\$([a-zA-Z_][a-zA-Z0-9_-]*)/', function ($m) {
                return $this->variables[$m[1]] ?? $m[0];
            }, $inner);
            return $inner;
        }, $scss);
    }

    private function expand_includes(string $scss): string
    {
        $max_iterations = 10;
        $prev_scss = '';

        while ($scss !== $prev_scss && $max_iterations > 0) {
            $prev_scss = $scss;
            $max_iterations--;

            // Find @include and extract full statement with balanced parens
            $result = '';
            $pos = 0;
            while (preg_match('/@include\s+([a-zA-Z_][a-zA-Z0-9_-]*)/', $scss, $m, PREG_OFFSET_CAPTURE, $pos)) {
                $match_start = $m[0][1];
                $mixin_name = $m[1][0];

                $result .= substr($scss, $pos, $match_start - $pos);

                // Find the end of this statement
                $stmt_start = $match_start;
                $search_pos = $match_start + strlen($m[0][0]);
                $len = strlen($scss);

                // Skip whitespace
                while ($search_pos < $len && $scss[$search_pos] === ' ') $search_pos++;

                $args_str = '';
                if ($search_pos < $len && $scss[$search_pos] === '(') {
                    // Extract args with balanced parens
                    $depth = 0;
                    $paren_start = $search_pos;
                    for ($i = $search_pos; $i < $len; $i++) {
                        if ($scss[$i] === '(') $depth++;
                        elseif ($scss[$i] === ')') {
                            $depth--;
                            if ($depth === 0) {
                                $args_str = substr($scss, $paren_start + 1, $i - $paren_start - 1);
                                $search_pos = $i + 1;
                                break;
                            }
                        }
                    }
                }

                // Skip whitespace to semicolon
                while ($search_pos < $len && $scss[$search_pos] === ' ') $search_pos++;
                if ($search_pos < $len && $scss[$search_pos] === ';') {
                    $search_pos++;
                }

                $pos = $search_pos;
                $stmt = substr($scss, $stmt_start, $pos - $stmt_start);

                if (!isset($this->mixins[$mixin_name])) {
                    $result .= '/* Mixin not found: ' . $mixin_name . ' */';
                    continue;
                }

                $mixin = $this->mixins[$mixin_name];
                $args = $this->parse_mixin_args($args_str);
                $body = $mixin['body'];

                $params = $mixin['params'];
                $local_vars = [];

                $vararg_param = null;
                $normal_params = [];
                foreach ($params as $p) {
                    if ($p['is_vararg']) {
                        $vararg_param = $p['name'];
                    } else {
                        $normal_params[] = $p;
                    }
                }

                foreach ($normal_params as $idx => $param) {
                    if (isset($args[$idx])) {
                        $local_vars[$param['name']] = $args[$idx];
                    } elseif (isset($args[$param['name']])) {
                        $local_vars[$param['name']] = $args[$param['name']];
                    } elseif ($param['default'] !== null) {
                        $local_vars[$param['name']] = $param['default'];
                    }
                }

                if ($vararg_param) {
                    $remaining = array_slice($args, count($normal_params));
                    $local_vars[$vararg_param] = implode(', ', $remaining);
                }

                foreach ($local_vars as $name => $value) {
                    $body = preg_replace('/\B\$' . preg_quote($name) . '\b/', $value, $body);
                }

                $result .= $body;
            }

            $scss = $result . substr($scss, $pos);
        }

        return $scss;
    }

    private function parse_mixin_args(string $args_str): array
    {
        $args = [];
        if (empty(trim($args_str))) return $args;

        // Parenthesis-aware comma splitting
        $parts = [];
        $current = '';
        $depth = 0;
        $len = strlen($args_str);
        for ($i = 0; $i < $len; $i++) {
            $ch = $args_str[$i];
            if ($ch === '(') $depth++;
            elseif ($ch === ')') $depth--;
            elseif ($ch === ',' && $depth === 0) {
                $parts[] = trim($current);
                $current = '';
                continue;
            }
            $current .= $ch;
        }
        if (trim($current) !== '') $parts[] = trim($current);

        foreach ($parts as $part) {
            if (empty($part)) continue;

            if (str_contains($part, ':') && !str_contains($part, '(')) {
                [$key, $value] = explode(':', $part, 2);
                $args[trim($key)] = trim($value);
            } else {
                $args[] = $part;
            }
        }

        return $args;
    }

    private function evaluate_functions(string $scss): string
    {
        // Handle built-in SCSS functions

        // hexToRGB(#hex) — custom function
        $scss = preg_replace_callback('/hexToRGB\s*\(\s*#([0-9a-fA-F]{3,6})\s*\)/', function ($m) {
            $hex = ltrim($m[1], '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return $r . ', ' . $g . ', ' . $b;
        }, $scss);

        // red($color)
        $scss = preg_replace_callback('/red\s*\(\s*#([0-9a-fA-F]{3,6})\s*\)/', function ($m) {
            $hex = ltrim($m[1], '#');
            if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            return (string)hexdec(substr($hex, 0, 2));
        }, $scss);

        // green($color)
        $scss = preg_replace_callback('/green\s*\(\s*#([0-9a-fA-F]{3,6})\s*\)/', function ($m) {
            $hex = ltrim($m[1], '#');
            if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            return (string)hexdec(substr($hex, 2, 2));
        }, $scss);

        // blue($color)
        $scss = preg_replace_callback('/blue\s*\(\s*#([0-9a-fA-F]{3,6})\s*\)/', function ($m) {
            $hex = ltrim($m[1], '#');
            if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            return (string)hexdec(substr($hex, 4, 2));
        }, $scss);

        return $scss;
    }

    private function flatten_nesting(string $scss): string
    {
        $lines = explode("\n", $scss);
        $result = [];
        $stack = [['selector' => '', 'rules' => []]];
        $current_rule = '';
        $root_rules = [];
        $in_block = 0;
        $block_buffer = '';
        $selector_stack = [];

        // Simple state machine approach
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if (empty($trimmed)) {
                $i++;
                continue;
            }

            // Check for top-level @-rules
            if ($in_block === 0 && (str_starts_with($trimmed, '@media') || str_starts_with($trimmed, '@keyframes') || str_starts_with($trimmed, '@font-face') || str_starts_with($trimmed, '@supports'))) {
                $block_content = $this->extract_block_content($lines, $i);
                $result[] = $block_content['text'];
                $i = $block_content['end'] + 1;
                continue;
            }

            // Check for top-level selector block
            if ($in_block === 0 && (str_contains($trimmed, '{'))) {
                $brace_pos = strpos($trimmed, '{');
                $selector = trim(substr($trimmed, 0, $brace_pos));
                $after_brace = substr($trimmed, $brace_pos + 1);

                // Parse block content
                $block_content = '';
                $depth = 1;
                $search_lines = $after_brace ? array_merge([$after_brace], array_slice($lines, $i + 1)) : array_slice($lines, $i + 1);
                $search_text = implode("\n", $search_lines);
                $pos = 0;
                $len = strlen($search_text);

                while ($pos < $len && $depth > 0) {
                    $ch = $search_text[$pos];
                    if ($ch === '{') $depth++;
                    elseif ($ch === '}') $depth--;
                    if ($depth > 0) {
                        $block_content .= $ch;
                    }
                    $pos++;
                }

                // Process the block content
                $processed = $this->process_nested_block($block_content, $selector);

                if (!empty($processed)) {
                    $result[] = $processed;
                }

                // Skip to after the block
                $consumed_lines = substr_count(substr($search_text, 0, $pos), "\n");
                $i += $consumed_lines + 1;
                continue;
            }

            // Just add any remaining content
            if ($in_block === 0 && !str_contains($trimmed, '@')) {
                $result[] = $trimmed;
            }

            $i++;
        }

        return implode("\n", array_filter($result, function ($v) {
            return trim($v) !== '';
        }));
    }

    private function process_nested_block(string $content, string $parent_selector): string
    {
        $lines = explode("\n", $content);
        $output = [];
        $nested_blocks = [];
        $direct_rules = [];
        $media_blocks = [];

        $i = 0;
        while ($i < count($lines)) {
            $trimmed = trim($lines[$i]);
            if (empty($trimmed)) { $i++; continue; }

            // @media inside a block
            if (str_starts_with($trimmed, '@media') || str_starts_with($trimmed, '@supports')) {
                $block = $this->extract_block_content($lines, $i);
                $media_blocks[] = [
                    'at_rule'  => $trimmed,
                    'content'  => $this->process_nested_block($block['extracted'], $parent_selector),
                ];
                $i = $block['end'] + 1;
                continue;
            }

            // Nested selector block
            if (str_contains($trimmed, '{')) {
                $brace_pos = strpos($trimmed, '{');
                $child_selector = trim(substr($trimmed, 0, $brace_pos));
                $after_brace = substr($trimmed, $brace_pos + 1);

                // Find the end of this block
                $search_lines = $after_brace ? array_merge([$after_brace], array_slice($lines, $i + 1)) : array_slice($lines, $i + 1);
                $search_text = implode("\n", $search_lines);
                $depth = 1;
                $pos = 0;
                $len = strlen($search_text);
                $block_body = '';

                while ($pos < $len && $depth > 0) {
                    $ch = $search_text[$pos];
                    if ($ch === '{') $depth++;
                    elseif ($ch === '}') $depth--;
                    if ($depth > 0) {
                        $block_body .= $ch;
                    }
                    $pos++;
                }

                // Combine selectors
                $combined = $this->combine_selectors($parent_selector, $child_selector);
                $processed = $this->process_nested_block($block_body, $combined);
                if (!empty(trim($processed))) {
                    $nested_blocks[] = $processed;
                }

                $consumed = substr_count(substr($search_text, 0, $pos), "\n");
                $i += $consumed + 1;
                continue;
            }

            // Property declaration
            if (str_ends_with($trimmed, ';') || str_ends_with($trimmed, ';')) {
                $direct_rules[] = $trimmed;
            } elseif (str_contains($trimmed, ':')) {
                $direct_rules[] = $trimmed . ';';
            }

            $i++;
        }

        // Output
        $block_output = [];

        if (!empty($direct_rules)) {
            $block_output[] = $parent_selector . ' {';
            foreach ($direct_rules as $rule) {
                $block_output[] = '  ' . $rule;
            }
            $block_output[] = '}';
        }

        foreach ($nested_blocks as $nb) {
            $block_output[] = $nb;
        }

        foreach ($media_blocks as $mb) {
            $media_selector = $mb['at_rule'];
            // Check if it already has { in it (has content directly)
            if (!str_contains($media_selector, '{')) {
                $block_output[] = $media_selector . ' {';
                $block_output[] = $mb['content'];
                $block_output[] = '}';
            } else {
                $block_output[] = $mb['content'];
            }
        }

        return implode("\n", $block_output);
    }

    private function combine_selectors(string $parent, string $child): string
    {
        $parent = trim($parent);
        $child = trim($child);

        if (empty($parent)) return $child;
        if (empty($child)) return $parent;

        // Handle & replacement
        if (str_contains($child, '&')) {
            return str_replace('&', $parent, $child);
        }

        // Simple concatenation
        $parent_parts = explode(',', $parent);
        $child_parts = explode(',', $child);
        $combined = [];

        foreach ($parent_parts as $pp) {
            $pp = trim($pp);
            foreach ($child_parts as $cp) {
                $cp = trim($cp);
                $combined[] = $pp . ' ' . $cp;
            }
        }

        return implode(', ', $combined);
    }

    private function extract_block_content(array $lines, int $start): array
    {
        $text = '';
        $extracted = '';
        $depth = 0;
        $started = false;
        $end = $start;

        for ($j = $start; $j < count($lines); $j++) {
            $line = $lines[$j];
            $text .= $line . "\n";
            $end = $j;

            $open_count = substr_count($line, '{');
            $close_count = substr_count($line, '}');

            if ($open_count > 0 && !$started) {
                // Extract content after first {
                $brace_pos = strpos($line, '{');
                $after = substr($line, $brace_pos + 1);
                if (!empty(trim($after))) {
                    $extracted .= $after . "\n";
                }
                $depth += $open_count - 1;
                $started = true;
                continue;
            }

            if ($started) {
                $depth += $open_count - $close_count;
                if ($depth < 0) {
                    // The closing brace was on this line, extract up to it
                    $close_pos = strrpos($line, '}');
                    $before = substr($line, 0, $close_pos);
                    if (!empty(trim($before))) {
                        $extracted .= $before;
                    }
                    break;
                }
                $extracted .= $line . "\n";
            }
        }

        return [
            'text'      => $text,
            'extracted' => trim($extracted),
            'end'       => $end,
        ];
    }

    private function clean_output(string $css): string
    {
        // Remove empty rules (selectors followed by only whitespace and })
        $css = preg_replace('/[^\n{}]+\{[ \t\n]*\}\n*/', '', $css);

        // Remove orphaned closing braces (extra } that don't balance)
        $lines = explode("\n", $css);
        $depth = 0;
        $result = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            $open = substr_count($trimmed, '{');
            $close = substr_count($trimmed, '}');
            if ($close > $open && $depth <= 0) {
                continue;
            }
            $result[] = $line;
            $depth += $open - $close;
            if ($depth < 0) $depth = 0;
        }
        $css = implode("\n", $result);

        // Remove excessive blank lines
        $css = preg_replace("/\n{3,}/", "\n\n", $css);

        // Fix calc() expressions that might have been mangled
        $css = preg_replace('/calc\s*\(\s*\$/', 'calc(', $css);

        return trim($css);
    }

    public function compile_file(string $file_path): string
    {
        if (!file_exists($file_path)) {
            throw new \RuntimeException('File not found: ' . $file_path);
        }

        $scss = file_get_contents($file_path);
        return $this->compile($scss, $file_path);
    }

    public function compile_directory(string $dir_path, string $entry_file = 'style.scss'): string
    {
        $entry_path = rtrim($dir_path, '/') . '/' . $entry_file;
        if (!file_exists($entry_path)) {
            throw new \RuntimeException('Entry file not found: ' . $entry_path);
        }
        return $this->compile_file($entry_path);
    }

    public function minify(string $css): string
    {
        return scss_minifier::minify($css);
    }

    public function get_variables(): array
    {
        return $this->variables;
    }

    private function normalize_path(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }
}
