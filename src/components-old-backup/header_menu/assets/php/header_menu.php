<?php

class HeaderMenu
{
    public static function render(array $data = []): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        $menuHtml = HeaderMenuPagesList::render([
            'menuData' => $data['menuData'] ?? [],
            'categoriesData' => $data['categoriesData'] ?? [],
            'globalData' => $data['globalData'] ?? [],
        ]);
        $searchHtml = SearchPostButton::render([]);
        $themeToggleHtml = ToggleTheme::render([]);

        $html = str_replace('{{ menu_list_html }}', $menuHtml, $html);
        $html = str_replace('{{ search_button_html }}', $searchHtml, $html);
        $html = str_replace('{{ theme_toggle_html }}', $themeToggleHtml, $html);
        $html = str_replace('{{ mobile_menu_icon_html }}', svg_get('menu'), $html);

        return $html;
    }
}
