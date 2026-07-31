<?php

class footer
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = get_data_json('data_global_settings', 'data');

        $siteIdentity = $jsonGlobalData['site_identity'] ?? '';
        $copyrightsHtml = '<div class="dm-footer-copyrights">&copy; ' . htmlspecialchars($siteIdentity) . '</div>';

        return self::render_template([
            'copyrights_html' => $copyrightsHtml,
        ]);
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
