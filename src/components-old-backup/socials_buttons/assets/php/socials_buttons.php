<?php

class SocialsButtons
{
    public static function render(array $data = []): string
    {
        $footerBlock = $data['footerBlock'] ?? [];
        $socialsData = $data['socialsData'] ?? [];

        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        $html = str_replace('{{ title_html }}', self::render_title($footerBlock), $html);
        $html = str_replace('{{ buttons_html }}', self::render_buttons($socialsData), $html);

        return $html;
    }

    protected static function render_title(array $footerBlock): string
    {
        if (empty($footerBlock['title'])) {
            return '';
        }
        return '<h5>' . htmlspecialchars($footerBlock['title'], ENT_QUOTES, 'UTF-8') . '</h5>';
    }

    protected static function render_buttons(array $socialsData): string
    {
        $html = '<div class="dm-socials-list" data-socials="circle-light-2">';
        foreach ($socialsData as $item) {
            $html .= '<a href="' . htmlspecialchars($item['url'] ?? '#', ENT_QUOTES, 'UTF-8') . '" target="_blank">';
            if (!empty($item['icon'])) {
                $html .= svg_get($item['icon']);
            }
            $html .= '</a>';
        }
        $html .= '</div>';
        return $html;
    }
}
