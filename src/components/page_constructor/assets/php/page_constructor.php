<?php

require_once __DIR__ . '/page_constructor_seo.php';

class page_constructor extends page_constructor_seo
{
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

        PlatformComponentRenderer::mark_used('overlay_noise');
        PlatformComponentRenderer::mark_used('animation_grid_background');
        $overlayNoise = PlatformComponentRenderer::render('overlay_noise');
        $animationGridBackground = PlatformComponentRenderer::render('animation_grid_background');

        $themeModeInit = '';
        $themeModeInitPath = __DIR__ . '/../js/theme_mode_init.js';
        if (file_exists($themeModeInitPath)) {
            $themeModeInit = file_get_contents($themeModeInitPath);
        }

        return PlatformTemplateRenderer::render([
            'lang_iso' => $langIso,
            'body_mode' => $defaultMode,
            'seo_fields' => $seoFields,
            'component_asset_tags' => $assetTags,
            'overlay_noise' => $overlayNoise,
            'animation_grid_background' => $animationGridBackground,
            'theme_mode_init' => $themeModeInit,
            'header_content' => $headerContent,
            'main_content' => $mainContent,
            'footer_content' => $footerContent,
        ]);
    }
}
