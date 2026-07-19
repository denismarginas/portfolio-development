<?php

class FooterLinklist
{
    public static function render(array $data = []): string
    {
        $footerBlock = $data['footerBlock'] ?? [];
        $items = [];
        $pageSlugExtension = $data['pageSlugExtension'] ?? '.html';
        $globalData = $data['globalData'] ?? [];
        $skipWrapper = $data['skipWrapper'] ?? false;

        if (isset($footerBlock['list']) && is_array($footerBlock['list'])) {
            $items = $footerBlock['list'];
        } elseif (isset($footerBlock['list']) && is_string($footerBlock['list']) && in_array($footerBlock['list'], ['projects', 'categories'], true)) {
            $items = $globalData['categories'] ?? [];
        }

        $title = htmlspecialchars($footerBlock['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $listHtml = self::render_items($items, $footerBlock, $pageSlugExtension);

        if ($skipWrapper) {
            return '<h5>' . $title . '</h5><ul>' . $listHtml . '</ul>';
        }

        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        $html = str_replace('{{ title }}', $title, $html);
        $html = str_replace('{{ list_items }}', $listHtml, $html);

        return $html;
    }

    protected static function render_items(array $items, array $footerBlock, string $pageSlugExtension): string
    {
        $html = '';
        $urlPath = $GLOBALS['urlPath'] ?? '';
        foreach ($items as $item) {
            $itemUrl = '#';
            if (isset($item['slug'])) {
                $itemUrl = htmlspecialchars($item['slug'] . $pageSlugExtension, ENT_QUOTES, 'UTF-8');
            } elseif (isset($item['url'])) {
                $itemUrl = htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8');
                if (str_starts_with($item['url'], 'content/')) {
                    $itemUrl = htmlspecialchars($urlPath . $item['url'], ENT_QUOTES, 'UTF-8');
                }
            }

            $html .= '<li>';
            $html .= '<a href="' . $itemUrl . '" target="_blank">';
            $html .= svg_get('chevron-right');
            if (!empty($item['icon'])) {
                $html .= svg_get($item['icon']);
            }
            $html .= '<span>' . htmlspecialchars($item['text'] ?? $item['name'] ?? '', ENT_QUOTES, 'UTF-8') . '</span>';
            $html .= '</a>';
            $html .= '</li>';
        }

        return $html;
    }
}
