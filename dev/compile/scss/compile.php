<?php

require_once __DIR__ . '/../../engine/bootstrap.php';

class ScssCompileManager
{
    private static ?array $config = null;

    public static function load_config(): array
    {
        if (self::$config !== null) return self::$config;

        $config_path = __DIR__ . '/scss-config.json';
        if (!file_exists($config_path)) {
            self::$config = [
                'clean_css_dir' => true,
                'minify' => true,
                'entries' => [
                    ['key' => 'style', 'label' => 'Theme Style', 'file' => 'src/theme/scss/style.scss', 'output' => 'style', 'enabled' => true, 'minify' => true],
                    ['key' => 'components', 'label' => 'Components', 'file' => '__components__', 'output' => 'components', 'enabled' => true, 'minify' => true],
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
        $global_minify = $config['minify'] ?? true;
        $entry_minify = $entry['minify'] ?? null;
        return $entry_minify !== null ? $entry_minify : $global_minify;
    }

    private static function use_min_suffix(array $entry): bool
    {
        $config = self::$config;
        $global_suffix = $config['minify_output_suffix'] ?? true;
        $entry_suffix = $entry['minify_output_suffix'] ?? null;
        return $entry_suffix !== null ? $entry_suffix : $global_suffix;
    }

    public static function compile(string $filter_key = null): array
    {
        $results = [];
        $config = self::load_config();
        $css_dir = EngineConfig::get('theme_css_dir');

        if (!empty($config['clean_css_dir']) && $filter_key === null) {
            if (is_dir($css_dir)) {
                foreach (glob($css_dir . '/*.css') as $file) {
                    unlink($file);
                }
            }
        }

        $entries = $config['entries'] ?? [];

        foreach ($entries as $entry) {
            $key = $entry['key'] ?? '';
            $enabled = $entry['enabled'] ?? true;
            $file = $entry['file'] ?? '';
            $output = $entry['output'] ?? $key;

            if ($filter_key !== null && $filter_key !== $key) continue;
            if (!$enabled) continue;

            try {
                if ($file === '__components__') {
                    $results[$key] = self::compile_components($css_dir, $output, $entry);
                } else {
                    $results[$key] = self::compile_file($file, $css_dir, $output, $entry);
                }
            } catch (\Exception $e) {
                $results[$key] = ['status' => 'error', 'reason' => $e->getMessage()];
            }
        }

        return $results;
    }

    private static function compile_file(string $relative_path, string $css_dir, string $output, array $entry): array
    {
        $abs_file = EngineConfig::get('root_dir') . '/' . ltrim($relative_path, '/');
        if (!file_exists($abs_file)) {
            return ['status' => 'error', 'reason' => 'File not found: ' . $relative_path];
        }

        $compiler = new ScssCompiler();
        $compiler->add_load_path(EngineConfig::get('theme_scss_dir'));
        $css = $compiler->compile_file($abs_file);

        file_put_contents($css_dir . '/' . $output . '.css', $css);

        $minified = null;
        $use_suffix = self::use_min_suffix($entry);
        if (self::should_minify($entry)) {
            $minified = $compiler->minify($css);
            $filename = $use_suffix ? $output . '.min.css' : $output . '.css';
            file_put_contents($css_dir . '/' . $filename, $minified);
        }

        return ['status' => 'compiled', 'size' => strlen($css), 'min' => $minified ? strlen($minified) : null];
    }

    private static function compile_components(string $css_dir, string $output, array $entry): array
    {
        $css = '';
        $components = DataService::get_all_component_names();
        $components_dir = EngineConfig::get('components_dir');

        foreach ($components as $component_name) {
            $scss_file = $components_dir . '/' . $component_name . '/assets/scss/style.scss';
            if (!file_exists($scss_file)) continue;

            try {
                $compiler = new ScssCompiler();
                $compiler->add_load_path(EngineConfig::get('theme_scss_dir'));
                $compiler->add_load_path(EngineConfig::get('theme_scss_dir') . '/functions');
                $compiler->add_load_path(EngineConfig::get('theme_scss_dir') . '/interface-design');
                $compiler->add_load_path(dirname($scss_file));
                $css .= $compiler->compile_file($scss_file) . "\n";
            } catch (\Exception $e) {
                $css .= '/* Error compiling ' . $component_name . ': ' . $e->getMessage() . ' */' . "\n";
            }
        }

        if (empty(trim($css))) {
            return ['status' => 'skipped', 'reason' => 'No component SCSS files found'];
        }

        file_put_contents($css_dir . '/' . $output . '.css', $css);

        $minified = null;
        $use_suffix = self::use_min_suffix($entry);
        if (self::should_minify($entry)) {
            $minified = (new ScssCompiler())->minify($css);
            $filename = $use_suffix ? $output . '.min.css' : $output . '.css';
            file_put_contents($css_dir . '/' . $filename, $minified);
        }

        return ['status' => 'compiled', 'size' => strlen($css), 'min' => $minified ? strlen($minified) : null];
    }
}

if (php_sapi_name() === 'cli' && !defined('IN_COMPILE_MANAGER')) {
    $args = array_slice($argv ?? [], 1);
    $filter = $args[0] ?? null;
    $result = ScssCompileManager::compile($filter);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
}