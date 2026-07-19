<?php

class Copyrights
{
    public static function render(array $data = []): string
    {
        $footerBlock = $data['footerBlock'] ?? [];
        $items = $footerBlock['list'] ?? [];

        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        $html = str_replace('{{ copyright_items }}', self::render_items($items), $html);

        return $html;
    }

    protected static function render_items(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            if (empty($item)) {
                continue;
            }
            $html .= '<span>' . execute_php_in_string($item) . '</span>';
        }
        return $html;
    }
}
