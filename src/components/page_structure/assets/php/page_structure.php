<?php

class PageStructure
{
    public static function render(array $data = []): string
    {
        $bodyContent = $data['body_content'] ?? $data['content'] ?? $data['children_html'] ?? '';
        $seo = $data['seo'] ?? [];

        $globalData = get_data_json('data_global_settings', 'data');
        $themeDir = $globalData['theme_active']['dir_name'] ?? '';
        $themePath = $GLOBALS['urlPath'] . ($globalData['themes_path'] ?? '') . ($themeDir !== '' ? '/' . $themeDir : '');

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

        $cssFile = file_exists(EngineConfig::get('theme_css_dir') . '/style.min.css') ? 'style.min.css' : 'style.css';
        $componentsCssFile = file_exists(EngineConfig::get('theme_css_dir') . '/components.min.css') ? 'components.min.css' : 'components.css';
        $cssPath = $themePath . '/css/' . $cssFile;
        $componentsCssPath = $themePath . '/css/' . $componentsCssFile;
        $jsFile = file_exists(EngineConfig::get('theme_js_dir') . '/theme_script.min.js') ? 'theme_script.min.js' : 'theme_script.js';
        $jsPath = $themePath . '/js/' . $jsFile;
        $componentsJsFile = file_exists(EngineConfig::get('theme_js_dir') . '/components.min.js') ? 'components.min.js' : 'components.js';
        $componentsJsPath = $themePath . '/js/' . $componentsJsFile;

        $componentAssetTags = get_component_asset_tags();
        $headerContent = render_component('header', $data);

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

        $footerContent = render_component('footer');

        $debugSection = render_component('section_debug');

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            [
                '{{ seo_fields }}', '{{ google_analytics }}',
                '{{ theme_css_path }}', '{{ components_css_path }}',
                '{{ component_asset_tags }}', '{{ theme_js_path }}', '{{ components_js_path }}',
                '{{ lang_iso }}',
                '{{ header_content }}',
                '{{ page_heading }}', '{{ cookie_notice }}',
                '{{ preloader }}', '{{ body_content }}',
                '{{ debug_section }}', '{{ footer_content }}'
            ],
            [
                $seoFields, $googleAnalytics,
                $cssPath, $componentsCssPath,
                $componentAssetTags, $jsPath, $componentsJsPath,
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
