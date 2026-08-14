<?php

class PlatformPathService
{
    public static function dist_relative_prefix(): string
    {
        if (($GLOBALS['render_target'] ?? '') !== 'dist') {
            return PlatformConfig::getUrlPath();
        }
        $current = $GLOBALS['dist_rel_path'] ?? '';
        $dir = $current !== '' ? dirname($current) : '.';
        if ($dir === '.' || $dir === '') {
            return '';
        }
        return str_repeat('../', substr_count($dir, '/') + 1);
    }

    public static function asset_relative_prefix(): string
    {
        if (($GLOBALS['render_target'] ?? '') !== 'dist') {
            return PlatformConfig::getUrlPath();
        }
        $current = $GLOBALS['dist_rel_path'] ?? '';
        $dir = $current !== '' ? dirname($current) : '.';
        $depth = ($dir === '.' || $dir === '') ? 0 : substr_count($dir, '/') + 1;
        return str_repeat('../', $depth + 1);
    }

    public static function post_link(string $slug): string
    {
        $globalData = PlatformDataService::get_data('settings_routing');
        $extension = $globalData['routing']['extension'] ?? $globalData['page_slug_extension'] ?? '.html';

        if (($GLOBALS['render_target'] ?? '') === 'dist') {
            $folder = trim(self::get_dist_folder($slug), '/');
            $prefix = self::dist_relative_prefix();
            return $prefix . ($folder !== '' ? $folder . '/' : '') . ltrim($slug, '/') . $extension;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($requestUri, '/dev/platform/preview/')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
            return $scheme . '://' . $host . '/dev/platform/preview/?_id=' . urlencode($slug);
        }

        $baseUrl = rtrim($globalData['url'] ?? '', '/');
        return $baseUrl . '/' . ltrim($slug, '/') . $extension;
    }

    private static function get_dist_folder(string $slug): string
    {
        $types = self::get_content_types();
        foreach (($types['post'] ?? []) as $type => $config) {
            $posts = PlatformDataService::get_all_posts_from_file($type);
            if ($posts) {
                foreach ($posts as $post) {
                    if (($post['_id'] ?? '') === $slug) {
                        return $config['root'] ?? '';
                    }
                }
            }
        }
        foreach (($types['taxonomy'] ?? []) as $type => $config) {
            $terms = PlatformDataService::get_data('taxonomy_' . $type);
            if ($terms) {
                foreach ($terms as $term) {
                    if (($term['_id'] ?? '') === $slug) {
                        return $config['root'] ?? '';
                    }
                }
            }
        }
        return '';
    }

    private static function get_content_types(): array
    {
        $data = PlatformDataService::get_data('settings_types');
        return $data ?? [];
    }

    public static function front_page_link(): string
    {
        $mappings = PlatformConfig::getRoutingMappings();
        $frontId = $mappings['front_post_id'] ?? 'home';
        return self::post_link($frontId);
    }

    public static function load_php_dir(string $dir): void
    {
        foreach (glob(rtrim($dir, '/\\') . '/*.php') as $filename) {
            require_once $filename;
        }
    }
}
