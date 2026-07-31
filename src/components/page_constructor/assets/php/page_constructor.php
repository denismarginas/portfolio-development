<?php

class page_constructor
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

        $compileAssets = $data['compile_assets'] ?? false;
        if ($compileAssets) {
            $urlPath = engine_config::getUrlPath();
            $assetTags = '<link rel="stylesheet" href="' . $urlPath . 'src/components/theme/assets_compiled/css/bundle.css">'
                       . '<script src="' . $urlPath . 'src/components/theme/assets_compiled/js/bundle.js"></script>';
        } else {
            component_renderer::mark_used('theme');
            $assetTags = get_component_asset_tags();
        }

        $headerContent = render_component('header', $data);

        $mainContent = render_component('page_content', [
            'body_content' => $bodyContent,
            'post_current_data' => $data['post_current_data'] ?? null,
        ]);

        $footerContent = render_component('footer');

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            [
                '{{ lang_iso }}',
                '{{ seo_fields }}', '{{ component_asset_tags }}',
                '{{ header_content }}',
                '{{ main_content }}',
                '{{ footer_content }}'
            ],
            [
                $langIso,
                $seoFields, $assetTags,
                $headerContent,
                $mainContent,
                $footerContent
            ],
            $template
        );
    }
}
