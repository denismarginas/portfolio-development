<?php

class simplelink_constructor
{
    public static function render(array $data = []): string
    {
        $link = $data['link'] ?? '';
        if ($link === '') {
            return '';
        }

        $url = self::resolve_url($link);

        $text = $data['text'] ?? '';
        if ($text === '') {
            return $url;
        }

        $attrs = '';
        if (!empty($data['class'])) {
            $attrs .= ' class="' . htmlspecialchars($data['class'], ENT_QUOTES, 'UTF-8') . '"';
        }
        if (!empty($data['target'])) {
            $attrs .= ' target="' . htmlspecialchars($data['target'], ENT_QUOTES, 'UTF-8') . '"';
        }

        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $attrs . '>'
             . htmlspecialchars($text)
             . '</a>';
    }

    protected static function resolve_url(string $link): string
    {
        if (preg_match('#^https?://#i', $link)) {
            return $link;
        }

        if (($GLOBALS['render_target'] ?? '') === 'dist') {
            $globalData = get_data_json('data_global_settings', 'data');
            $extension = $globalData['page_slug_extension'] ?? '.html';
            $folder = data_service::get_post_by_id('projects', $link) !== null ? 'project/' : '';
            return self::dist_relative_link($folder . ltrim($link, '/') . $extension);
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($requestUri, '/dev/platform/preview/')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
            return $scheme . '://' . $host . '/dev/platform/preview/?post_id=' . urlencode($link);
        }

        $globalData = get_data_json('data_global_settings', 'data');
        $baseUrl = rtrim($globalData['url'] ?? '', '/');
        $extension = $globalData['page_slug_extension'] ?? '.html';
        return $baseUrl . '/' . ltrim($link, '/') . $extension;
    }

    protected static function dist_relative_link(string $target): string
    {
        $current = $GLOBALS['dist_rel_path'] ?? '';
        $dir = $current !== '' ? dirname($current) : '.';
        if ($dir === '.' || $dir === '') {
            return $target;
        }
        return str_repeat('../', substr_count($dir, '/') + 1) . $target;
    }
}
