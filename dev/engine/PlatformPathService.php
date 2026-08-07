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
            $folder = PlatformDataService::get_post_by_id('projects', $slug) !== null ? 'project/' : '';
            return self::dist_relative_prefix() . $folder . ltrim($slug, '/') . $extension;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($requestUri, '/dev/platform/preview/')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
            return $scheme . '://' . $host . '/dev/platform/preview/?post_id=' . urlencode($slug);
        }

        $baseUrl = rtrim($globalData['url'] ?? '', '/');
        return $baseUrl . '/' . ltrim($slug, '/') . $extension;
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
