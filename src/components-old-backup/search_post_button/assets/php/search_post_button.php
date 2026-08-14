<?php

class SearchPostButton
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = get_data_json('data_global_settings', 'data');
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        $html = str_replace('{{ search_url }}', htmlspecialchars($jsonGlobalData['search_page']['slug'] . $jsonGlobalData['page_slug_extension'], ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('{{ svg_search }}', svg_get('search'), $html);
        return $html;
    }
}
