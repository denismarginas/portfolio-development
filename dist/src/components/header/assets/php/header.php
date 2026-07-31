<?php

class header
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = get_data_json('data_global_settings', 'data');
        $jsonMenuData = get_data_json('data_menu', 'data');

        $siteIdentity = $jsonGlobalData['site_identity'] ?? '';
        $logoHtml = '<div class="dm-header-identity">' . htmlspecialchars($siteIdentity) . '</div>';

        $menuHtml = '';
        foreach (($jsonMenuData['menu_list'] ?? []) as $item) {
            $name = $item['name'] ?? '';
            $slug = $item['slug'] ?? '';
            if ($name === '' || $slug === '') continue;
            $menuHtml .= '<li><a href="' . self::link_url($slug) . '">' . htmlspecialchars($name) . '</a></li>';
        }
        $menuHtml = $menuHtml !== '' ? '<nav class="dm-header-menu"><ul>' . $menuHtml . '</ul></nav>' : '';

        return self::render_template([
            'logo_html' => $logoHtml,
            'menu_html' => $menuHtml,
            'page_heading' => !empty($data['page_heading']) ? '<h1 class="page-heading">' . htmlspecialchars($data['page_heading']) . '</h1>' : '',
        ]);
    }

    protected static function link_url(string $slug): string
    {
        if (($GLOBALS['render_target'] ?? '') === 'dist') {
            $globalData = get_data_json('data_global_settings', 'data');
            $extension = $globalData['page_slug_extension'] ?? '.html';
            $folder = data_service::get_post_by_id('projects', $slug) !== null ? 'project/' : '';
            return self::dist_relative_link($folder . ltrim($slug, '/') . $extension);
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($requestUri, '/dev/platform/preview/')) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
            return $scheme . '://' . $host . '/dev/platform/preview/?post_id=' . urlencode($slug);
        }
        $jsonGlobalData = get_data_json('data_global_settings', 'data');
        $baseUrl = rtrim($jsonGlobalData['url'] ?? '', '/');
        $extension = $jsonGlobalData['page_slug_extension'] ?? '.html';
        return $baseUrl . '/' . ltrim($slug, '/') . $extension;
    }

    protected static function dist_relative_link(string $target): string
    {
        return get_asset_relative_prefix() . $target;
    }

    protected static function render_template(array $data): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }

        return $html;
    }
}
