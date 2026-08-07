<?php

trait PlatformComponentRendererLoader
{
    public static function render(string $component_name, array $data = []): string
    {
        $config = self::get_component_config($component_name);
        if ($config === null) {
            return self::render_error('component_not_found', ['name' => $component_name]);
        }

        self::mark_used($component_name);

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
        if (class_exists('PlatformErrorRenderer')) {
            return PlatformErrorRenderer::render($key, $params);
        }
        return '';
    }

    public static function render_component(string $component_name, array $data = []): string
    {
        return self::render($component_name, $data);
    }

    public static function value(string $component_name, array $data = []): mixed
    {
        $config = self::get_component_config($component_name);
        if ($config === null) {
            return null;
        }

        self::mark_used($component_name);

        $dependencies = $config['dependencies'] ?? [];
        foreach ($dependencies as $dep) {
            self::load_component_class($dep);
        }

        $php_files = $config['assets']['php'] ?? [];
        if (empty($php_files)) {
            return null;
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
                return null;
            }
            self::$loaded_classes[$class_name] = true;
        }

        if (!class_exists($class_name, false)) {
            return null;
        }

        $component = new $class_name();
        if (method_exists($component, 'value')) {
            return $component->value($data);
        }
        if (method_exists($class_name, 'value')) {
            return $class_name::value($data);
        }
        if (method_exists($component, 'render')) {
            return $component->render($data);
        }
        if (method_exists($class_name, 'render')) {
            return $class_name::render($data);
        }

        return null;
    }
}
