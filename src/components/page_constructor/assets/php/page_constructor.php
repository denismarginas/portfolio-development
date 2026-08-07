<?php

require_once __DIR__ . '/page_constructor_seo.php';

class page_constructor
{
    use page_constructor_seo;

    public static function render(array $data = []): string
    {
        $bodyContent = $data['body_content'] ?? $data['content'] ?? $data['children_html'] ?? '';
        $seo = $data['seo'] ?? [];

        $globalData = PlatformDataService::get_data('settings_languages');

        $langIso = $globalData['default'] ?? '';

        $defaultMode = $data['mode'] ?? PlatformConfig::get('theme_default_mode', 'light');

        $seoFields = '';
        foreach (self::implicit_seo_fields() as $field) {
            $seoFields .= $field;
        }
        if (!empty($seo)) {
            $seoFields = self::add_seo_to_html($seo, $seoFields);
        }

        $compileAssets = $data['compile_assets'] ?? false;
        if ($compileAssets) {
            $urlPath = PlatformPathService::asset_relative_prefix();
            $compiledPath = PlatformConfig::get('theme_compiled_path');
            $assetTags = '<link rel="stylesheet" href="' . $urlPath . $compiledPath . '/css/bundle.css">'
                       . '<script src="' . $urlPath . $compiledPath . '/js/bundle.js"></script>';
        } else {
            PlatformComponentRenderer::mark_used('theme');
            $assetTags = PlatformComponentRenderer::get_component_asset_tags();
        }

        $headerContent = PlatformComponentRenderer::render('header', $data);

        $mainContent = PlatformComponentRenderer::render('page_content', [
            'body_content' => $bodyContent,
            'post_current_data' => $data['post_current_data'] ?? null,
        ]);

        $footerContent = PlatformComponentRenderer::render('footer');

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            [
                '{{ lang_iso }}',
                '{{ body_mode }}',
                '{{ seo_fields }}', '{{ component_asset_tags }}',
                '{{ header_content }}',
                '{{ main_content }}',
                '{{ footer_content }}'
            ],
            [
                $langIso,
                $defaultMode,
                $seoFields, $assetTags,
                $headerContent,
                $mainContent,
                $footerContent
            ],
            $template
        );
    }
}
