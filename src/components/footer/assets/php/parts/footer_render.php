<?php

trait footer_render
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = PlatformDataService::get_data('settings_site');
        $siteIdentity = $jsonGlobalData['name'] ?? '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template.html', [
            'copyrights_html' => self::render_copyrights($siteIdentity),
        ]);
    }
}
