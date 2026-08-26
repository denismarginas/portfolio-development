<?php

class footer_render extends footer_copyrights
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = PlatformDataService::get_data('settings_site');
        $siteIdentity = $jsonGlobalData['name'] ?? '';

        return PlatformTemplateRenderer::render([
            'copyrights_html' => self::render_copyrights($siteIdentity),
        ]);
    }
}




