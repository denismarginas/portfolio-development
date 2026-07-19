<?php

class Seo
{
    public static function render(array $data = []): string
    {
        $seoData = $data['seo'] ?? $data;

        $html = '';
        $implicitFields = seo_implicit_fields();
        foreach ($implicitFields as $field) {
            $html .= $field;
        }

        if (!empty($seoData)) {
            $html = seo_add_in_content($seoData, $html);
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ seo_html }}', $html, $template);
    }

    public static function get_page_seo(string $filename): ?array
    {
        return get_seo_from_current_page_data($filename);
    }

    public static function get_post_seo(array $postData): ?array
    {
        return get_seo_from_current_post_project_data($postData);
    }
}
