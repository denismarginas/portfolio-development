<?php

trait header_menu
{
    protected static function render_menu(array $data = []): string
    {
        $jsonContent = PlatformDataService::get_data('content_header_menu');
        $menuList = $jsonContent['menu_list'] ?? [];

        $currentPostId = $data['post_current_data']['_id'] ?? '';

        $items = '';

        foreach ($menuList as $item) {
            $postId = $item['_id'] ?? '';
            $text = $item['text'] ?? '';
            if ($postId === '' || $text === '') continue;

            $submenuHtml = '';
            foreach (($item['submenu'] ?? []) as $subItem) {
                $subId = $subItem['_id'] ?? '';
                $subText = $subItem['text'] ?? '';
                if ($subId === '' || $subText === '') continue;
                $submenuHtml .= PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_menu_item.html', [
                    'active' => self::menu_active_attr($subId, $currentPostId),
                    'url' => PlatformPathService::post_link($subId),
                    'name' => htmlspecialchars($subText),
                    'submenu' => '',
                ]);
            }

            if ($submenuHtml !== '') {
                $submenuHtml = '<ul class="header-submenu">' . $submenuHtml . '</ul>';
            }

            $items .= PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_menu_item.html', [
                'active' => self::menu_active_attr($postId, $currentPostId),
                'url' => PlatformPathService::post_link($postId),
                'name' => htmlspecialchars($text),
                'submenu' => $submenuHtml,
            ]);
        }

        if ($items === '') {
            return '';
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_menu.html', [
            'items' => $items,
            'search_button_html' => self::render_search_button($data),
            'theme_toggle_html' => self::render_theme_toggle(),
            'menu_toggle_html' => self::render_menu_toggle(),
        ]);
    }

    protected static function menu_active_attr(string $postId, string $currentPostId): string
    {
        if ($currentPostId !== '' && $postId === $currentPostId) {
            return 'class="active" aria-current="page"';
        }
        return '';
    }

    protected static function render_menu_toggle(): string
    {
        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_menu_navbar_toggle.html', [
            'menu_icon_html' => PlatformComponentRenderer::render('svg', [
                'icon' => 'menu',
                'class' => 'header-menu-navbar-toggle-icon',
            ]),
        ]);
    }
}
