<?php

require_once __DIR__ . '/../../engine/bootstrap.php';

class JsCompileManager
{
    private static ?array $config = null;

    public static function load_config(): array
    {
        if (self::$config !== null) return self::$config;

        $config_path = __DIR__ . '/js-config.json';
        if (!file_exists($config_path)) {
            self::$config = [
                'clean_output' => true,
                'minify' => false,
                'entries' => [
                    ['key' => 'components', 'label' => 'Component JS', 'type' => '__components__', 'output' => 'components', 'enabled' => true, 'minify' => true],
                ],
            ];
            return self::$config;
        }

        self::$config = json_decode(file_get_contents($config_path), true) ?? [];
        return self::$config;
    }

    private static function should_minify(array $entry): bool
    {
        $config = self::$config;
        $global = $config['minify'] ?? false;
        $entry_val = $entry['minify'] ?? null;
        return $entry_val !== null ? $entry_val : $global;
    }

    private static function use_min_suffix(array $entry): bool
    {
        $config = self::$config;
        $global = $config['minify_output_suffix'] ?? false;
        $entry_val = $entry['minify_output_suffix'] ?? null;
        return $entry_val !== null ? $entry_val : $global;
    }

    public static function compile(string $filter_key = null): array
    {
        $results = [];
        $config = self::load_config();
        $js_dir = EngineConfig::get('theme_js_dir');

        if (!empty($config['clean_output']) && $filter_key === null) {
            foreach (glob($js_dir . '/components*.js') as $file) {
                unlink($file);
            }
        }

        $entries = $config['entries'] ?? [];

        foreach ($entries as $entry) {
            $key = $entry['key'] ?? '';
            $enabled = $entry['enabled'] ?? true;
            $type = $entry['type'] ?? '';
            $output = $entry['output'] ?? $key;

            if ($filter_key !== null && $filter_key !== $key) continue;
            if (!$enabled) continue;

            try {
                if ($type === '__components__') {
                    $results[$key] = self::compile_components($js_dir, $output, $entry);
                }
            } catch (\Exception $e) {
                $results[$key] = ['status' => 'error', 'reason' => $e->getMessage()];
            }
        }

        return $results;
    }

    private static function compile_components(string $js_dir, string $output, array $entry): array
    {
        $js = '';
        $components = DataService::get_all_component_names();
        $components_dir = EngineConfig::get('components_dir');
        $count = 0;

        foreach ($components as $component_name) {
            $config = DataService::get_component_config($component_name);
            if ($config === null) continue;

            $js_files = $config['assets']['js'] ?? [];
            foreach ($js_files as $js_file) {
                $file_path = $components_dir . '/' . $component_name . '/' . $js_file;
                if (!file_exists($file_path)) continue;

                $content = file_get_contents($file_path);
                $js .= "/* --- {$component_name}/{$js_file} --- */\n" . $content . "\n\n";
                $count++;
            }
        }

        if (empty(trim($js))) {
            return ['status' => 'skipped', 'reason' => 'No component JS files found'];
        }

        file_put_contents($js_dir . '/' . $output . '.js', $js);

        if (self::should_minify($entry)) {
            $minified = self::minify_js($js);
            $filename = self::use_min_suffix($entry) ? $output . '.min.js' : $output . '.js';
            file_put_contents($js_dir . '/' . $filename, $minified);
        }

        return ['status' => 'compiled', 'files' => $count, 'size' => strlen($js)];
    }

    private static function minify_js(string $js): string
    {
        $js = preg_replace('/\/\/.*$/m', '', $js);
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        $js = preg_replace('/\n+/', "\n", $js);
        $js = preg_replace('/[ \t]+/', ' ', $js);
        $js = preg_replace('/\s*([{}();,=+\-*\/%!<>|&?:])\s*/', '$1', $js);
        $js = preg_replace('/;\s*\}/', '}', $js);
        return trim($js);
    }
}

if (php_sapi_name() === 'cli' && !defined('IN_JS_COMPILE_MANAGER')) {
    $args = array_slice($argv ?? [], 1);
    $filter = $args[0] ?? null;
    $result = JsCompileManager::compile($filter);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
}