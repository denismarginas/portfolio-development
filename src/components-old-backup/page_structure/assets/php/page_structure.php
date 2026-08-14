<?php

class PageStructure
{
    public static function render(array $data = []): string
    {
        $bodyContent = $data['body_content'] ?? $data['content'] ?? $data['children_html'] ?? '';
        $seo = $data['seo'] ?? [];

        $globalData = get_data_json('data_global_settings', 'data');

        $langIso = '';
        $defaultLang = $globalData['language']['default'] ?? '';
        foreach (($globalData['language']['list'] ?? []) as $lang) {
            if (($lang['text'] ?? '') === $defaultLang) {
                $langIso = $lang['iso'] ?? '';
                break;
            }
        }

        $seoFields = '';
        foreach (seo_implicit_fields() as $field) {
            $seoFields .= $field;
        }
        if (!empty($seo)) {
            $seoFields = seo_add_in_content($seo, $seoFields);
        }

        $googleAnalytics = render_component('google_analytics');

        $compileAssets = $data['compile_assets'] ?? false;
        if ($compileAssets) {
            $urlPath = EngineConfig::getUrlPath();
            $assetTags = '<link rel="stylesheet" href="' . $urlPath . 'src/components/theme/assets_compiled/css/bundle.css">'
                       . '<script src="' . $urlPath . 'src/components/theme/assets_compiled/js/bundle.js"></script>';
        } else {
            ComponentRenderer::mark_used('theme');
            $assetTags = get_component_asset_tags();
        }

        $headerComponent = $data['header_component'] ?? 'header';
        $headerContent = render_component($headerComponent, $data);

        $pageHeading = '';
        if (!empty($seo['title'])) {
            $pageHeading = '<h1 class="page-heading">' . htmlspecialchars($seo['title']) . '</h1>';
        }

        $cookieNotice = '';
        if (isset($globalData['cookie_notice'])) {
            $cookieNotice = render_component('cookie_notice');
        }

        $preloader = '';
        if (!empty($globalData['theme_active']['preloader']) && $globalData['theme_active']['preloader'] === 'true') {
            $preloader = render_component('animation_preloader');
        }

        $footerComponent = $data['footer_component'] ?? 'footer';
        $footerContent = render_component($footerComponent);

        $debugSection = render_component('section_debug');

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            [
                '{{ seo_fields }}', '{{ google_analytics }}',
                '{{ component_asset_tags }}',
                '{{ lang_iso }}',
                '{{ header_content }}',
                '{{ page_heading }}', '{{ cookie_notice }}',
                '{{ preloader }}', '{{ body_content }}',
                '{{ debug_section }}', '{{ footer_content }}'
            ],
            [
                $seoFields, $googleAnalytics,
                $assetTags,
                $langIso,
                $headerContent,
                $pageHeading, $cookieNotice,
                $preloader, $bodyContent,
                $debugSection, $footerContent
            ],
            $template
        );
    }
}
