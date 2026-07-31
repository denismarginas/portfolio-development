<?php

class component_renderer
{
    private static array $component_cache = [];
    private static array $loaded_classes = [];
    private static array $used_components = [];

    public static function render(string $component_name, array $data = []): string
    {
        $config = self::get_component_config($component_name);
        if ($config === null) {
            return self::render_error('component_not_found', ['name' => $component_name]);
        }

        // Track this component and its dependencies as used
        self::mark_used($component_name);

        // Auto-load dependencies first
        $dependencies = $config['dependencies'] ?? [];
        foreach ($dependencies as $dep) {
            self::load_component_class($dep);
        }

        $php_files = $config['assets']['php'] ?? [];
        if (empty($php_files)) {
            return self::render_error('no_php_files', ['name' => $component_name]);
        }

        $class_name = self::component_name_to_class($component_name);
        if (!isset(self::$loaded_classes[$class_name])) {
            $loaded = false;
            foreach ($php_files as $php_file) {
                $file_path = self::get_component_file_path($component_name, $php_file);
                if ($file_path && file_exists($file_path)) {
                    require_once $file_path;
                    $loaded = true;
                }
            }

            if (!$loaded || !class_exists($class_name, false)) {
                return self::render_error('class_not_found', ['class' => $class_name]);
            }

            self::$loaded_classes[$class_name] = true;
        }

        if (!class_exists($class_name, false)) {
            return self::render_error('class_not_loaded', ['class' => $class_name]);
        }

        $component = new $class_name();

        if (method_exists($component, 'render')) {
            return $component->render($data);
        }

        if (method_exists($class_name, 'render')) {
            return $class_name::render($data);
        }

        return self::render_error('no_render_method', ['class' => $class_name]);
    }

    private static function render_error(string $key, array $params = []): string
    {
        if (class_exists('error_renderer')) {
            return error_renderer::render($key, $params);
        }
        return '';
    }

    public static function render_component(string $component_name, array $data = []): string
    {
        return self::render($component_name, $data);
    }

    public static function get_component_config(string $component_name): ?array
    {
        if (isset(self::$component_cache[$component_name])) {
            return self::$component_cache[$component_name];
        }

        $config = data_service::get_component_config($component_name);
        if ($config === null) {
            return null;
        }

        self::$component_cache[$component_name] = $config;
        return $config;
    }

    public static function get_component_asset_tags(): string
    {
        $html = '';
        $components_dir = engine_config::get('components_dir');
        $url_path = get_asset_relative_prefix();

        $names = !empty(self::$used_components) ? self::$used_components : data_service::get_all_component_names();

        foreach ($names as $name) {
            $config = self::get_component_config($name);
            if ($config === null) continue;

            $assets = $config['assets'] ?? [];

            foreach ($assets['css'] ?? [] as $css_file) {
                if (str_ends_with($css_file, '.scss')) continue;
                $file_path = $components_dir . '/' . $name . '/' . $css_file;
                if (file_exists($file_path)) {
                    $html .= '<link rel="stylesheet" href="' . $url_path . 'src/components/' . $name . '/' . $css_file . '">';
                }
            }

            foreach ($assets['js'] ?? [] as $js_file) {
                $file_path = $components_dir . '/' . $name . '/' . $js_file;
                if (file_exists($file_path)) {
                    $html .= '<script src="' . $url_path . 'src/components/' . $name . '/' . $js_file . '"></script>';
                }
            }
        }

        return $html;
    }

    public static function mark_used(string $component_name): void
    {
        if (!in_array($component_name, self::$used_components, true)) {
            self::$used_components[] = $component_name;
        }
        foreach (self::get_dependency_chain($component_name) as $dep) {
            if (!in_array($dep, self::$used_components, true)) {
                self::$used_components[] = $dep;
            }
        }
    }

    public static function get_dependency_chain(string $component_name, array &$visited = []): array
    {
        $deps = [];
        if (isset($visited[$component_name])) return $deps;
        $visited[$component_name] = true;

        $config = self::get_component_config($component_name);
        if ($config === null) return $deps;

        $direct_deps = $config['dependencies'] ?? [];
        foreach ($direct_deps as $dep) {
            $child_deps = self::get_dependency_chain($dep, $visited);
            $deps = array_merge($deps, $child_deps);
            if (!in_array($dep, $deps)) {
                $deps[] = $dep;
            }
        }

        return $deps;
    }

    public static function component_name_to_class(string $name): string
    {
        return str_replace('-', '_', $name);
    }

    public static function load_component_class(string $component_name): bool
    {
        $class_name = self::component_name_to_class($component_name);
        if (isset(self::$loaded_classes[$class_name])) {
            return true;
        }

        $config = self::get_component_config($component_name);
        if ($config === null) {
            return false;
        }

        // Load dependencies first
        $dependencies = $config['dependencies'] ?? [];
        foreach ($dependencies as $dep) {
            self::load_component_class($dep);
        }

        $php_files = $config['assets']['php'] ?? [];
        foreach ($php_files as $php_file) {
            $file_path = self::get_component_file_path($component_name, $php_file);
            if ($file_path && file_exists($file_path)) {
                require_once $file_path;
            }
        }

        self::$loaded_classes[$class_name] = true;
        return true;
    }

    protected static function get_component_file_path(string $component_name, string $relative_path): string
    {
        $components_dir = engine_config::get('components_dir');
        return $components_dir . '/' . $component_name . '/' . $relative_path;
    }
}
