<?php

class data_service
{
    private static array $cache = [];
    private static ?string $data_dir = null;

    public static function init(): void
    {
        self::$data_dir = engine_config::get('data_dir');
    }

    public static function get_data(string $name, string $sub_dir = 'data'): ?array
    {
        $cache_key = $sub_dir . '/' . $name;

        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $base_dir = engine_config::get('data_dir');
        if ($sub_dir !== 'data') {
            $base_dir = dirname($base_dir) . '/' . $sub_dir;
        }

        $file_path = $base_dir . '/data_' . $name . '.json';
        if (!file_exists($file_path)) {
            $file_path = $base_dir . '/' . $name . '.json';
        }
        if (!file_exists($file_path)) {
            $file_path = $base_dir . '/data-' . $name . '.json';
        }

        if (!file_exists($file_path)) {
            return null;
        }

        $content = file_get_contents($file_path);
        if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
            $content = substr($content, 3);
        }
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        self::$cache[$cache_key] = $data;
        return $data;
    }

    public static function get_index_data(string $name): ?array
    {
        return self::get_data($name, 'index');
    }

    public static function get_global_settings(): ?array
    {
        return self::get_data('global_settings');
    }

    public static function get_personal_data(): ?array
    {
        return self::get_data('content_personal');
    }

    public static function get_url_path(): string
    {
        return $GLOBALS['url_path'] ?? '';
    }

    public static function get_valid_post_files(): array
    {
        $data_dir = engine_config::get('data_dir');
        $files = glob($data_dir . '/data_post_*.json');
        $valid = [];

        foreach ($files as $file_path) {
            $filename = basename($file_path);
            // Must be data_post_{single_word}.json (no underscores after data_post_)
            if (preg_match('/^data_post_([a-z0-9]+)\.json$/', $filename, $matches)) {
                $valid[] = [
                    'name'     => $matches[1],
                    'path'     => $file_path,
                    'filename' => $filename,
                ];
            }
        }

        return $valid;
    }

    public static function get_all_posts_from_file(string $post_name): ?array
    {
        return self::get_data('post_' . $post_name);
    }

    public static function get_post_by_id(string $post_name, string $post_id): ?array
    {
        $posts = self::get_all_posts_from_file($post_name);
        if (empty($posts)) return null;

        foreach ($posts as $post) {
            if (($post['post_id'] ?? '') === $post_id) {
                return $post;
            }
        }

        return null;
    }

    public static function get_post_by_slug(string $post_name, string $slug): ?array
    {
        $posts = self::get_all_posts_from_file($post_name);
        if (empty($posts)) return null;

        foreach ($posts as $post) {
            $seo = $post['seo'] ?? [];
            if (($seo['slug'] ?? '') === $slug) {
                return $post;
            }
        }

        return null;
    }

    public static function get_component_config(string $component_name): ?array
    {
        $components_dir = engine_config::get('components_dir');
        $config_path = $components_dir . '/' . $component_name . '/component.json';

        if (!file_exists($config_path)) {
            return null;
        }

        $content = file_get_contents($config_path);
        if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
            $content = substr($content, 3);
        }
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data;
    }

    public static function get_all_component_names(): array
    {
        $components_dir = engine_config::get('components_dir');
        $items = scandir($components_dir);
        $components = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $config_path = $components_dir . '/' . $item . '/component.json';
            if (is_dir($components_dir . '/' . $item) && file_exists($config_path)) {
                $components[] = $item;
            }
        }

        sort($components);
        return $components;
    }

    public static function clear_cache(): void
    {
        self::$cache = [];
    }
}
