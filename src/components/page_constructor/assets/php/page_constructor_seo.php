<?php

trait page_constructor_seo
{
    protected static function implicit_seo_fields(): array
    {
        $fields = [];
        $global = PlatformDataService::get_global_settings();

        $site_name = $global['name'] ?? '';
        $description = $global['site_description'] ?? '';
        $favicon = $global['favicon'] ?? '';

        if (!empty($favicon)) {
            if (str_starts_with($favicon, 'http')) {
                $favicon_url = $favicon;
            } else {
                $favicon_url = PlatformPathService::asset_relative_prefix() . ltrim($favicon, '/');
            }
            $fields[] = '<link rel="icon" type="image/x-icon" href="' . htmlspecialchars($favicon_url) . '">';
        }
        if (!empty($site_name)) {
            $fields[] = '<meta name="application-name" content="' . htmlspecialchars($site_name) . '">';
        }
        if (!empty($description)) {
            $fields[] = '<meta name="description" content="' . htmlspecialchars($description) . '">';
        }
        $fields[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $fields[] = '<meta charset="UTF-8">';

        $gtag = $global['google_analytics']['id'] ?? '';
        if (!empty($gtag)) {
            $fields[] = '<meta name="google-analytics" content="' . htmlspecialchars($gtag) . '">';
        }
        $verification = $global['google_site_verification'] ?? '';
        if (!empty($verification)) {
            $fields[] = '<meta name="google-site-verification" content="' . htmlspecialchars($verification) . '">';
        }

        return $fields;
    }

    protected static function add_seo_to_html(array $seo_data, string $existing_html = ''): string
    {
        $html = $existing_html;

        if (!empty($seo_data['title'])) {
            $html .= '<title>' . htmlspecialchars($seo_data['title']) . '</title>';
        }

        foreach (['description' => 'description', 'keywords' => 'keywords'] as $key => $meta) {
            if (!empty($seo_data[$key])) {
                $html .= '<meta name="' . htmlspecialchars($meta) . '" content="' . htmlspecialchars($seo_data[$key]) . '">';
            }
        }

        $slug = $seo_data['slug'] ?? '';
        if (!empty($slug)) {
            $ext = PlatformConfig::getPageSlugExtension();
            $html .= '<link rel="canonical" href="' . htmlspecialchars($slug . $ext) . '">';
            $index = $seo_data['index'] ?? 'true';
            $html .= ($index === 'true' || $index === true)
                ? '<meta name="robots" content="index, follow">'
                : '<meta name="robots" content="noindex, nofollow">';
        }

        return $html;
    }
}
