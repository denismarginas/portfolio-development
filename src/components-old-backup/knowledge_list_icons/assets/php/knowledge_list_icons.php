<?php

class KnowledgeListIcons
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $items = $data['knowledgeItems'] ?? get_data_json('data_content_personal', 'data')["experience"]["knowledge_lists_items"] ?? [];

        if (empty($items)) {
            return '';
        }

        $itemsList = '';
        $i = 1;
        foreach ($items as $knowledge_item) {
            $url = isset($knowledge_item['url']) ? htmlspecialchars($knowledge_item['url']) : '#';
            $target = isset($knowledge_item['url']) ? ' target="_blank"' : '';
            $itemsList .= '<li data-motion="transition-fade-0 transition-slideInRight-0" data-duration="' . (0.04 * $i) . 's">';
            $itemsList .= '<a href="' . $url . '"' . $target . '>';
            $itemsList .= svg_get($knowledge_item['icon']);
            $itemsList .= '</a>';
            $itemsList .= '</li>';
            $i++;
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ items_list }}', $itemsList, $template);
    }
}
