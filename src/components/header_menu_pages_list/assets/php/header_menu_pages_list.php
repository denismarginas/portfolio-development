<?php

class HeaderMenuPagesList
{
    public static function render(array $data = []): string
    {
        $menuItems = $data['menuData']['menu_list'] ?? [];
        $categoriesData = $data['categoriesData'] ?? [];
        $globalData = $data['globalData'] ?? [];

        $html = self::render_list($menuItems, $categoriesData, $globalData);
        return $html;
    }

    protected static function render_list(array $menuItems, array $categoriesData, array $globalData): string
    {
        $html = '';

        foreach ($menuItems as $menuItem) {
            if (isset($menuItem['name']) && $menuItem['name'] === ($categoriesData['title'] ?? '')) {
                $html .= '<li>';
                $html .= '<a href="' . htmlspecialchars(($categoriesData['slug'] ?? '') . ($globalData['page_slug_extension'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
                $html .= htmlspecialchars($categoriesData['title'] ?? '', ENT_QUOTES, 'UTF-8');
                $html .= '</a>';

                if (!empty($categoriesData['categories']) && is_array($categoriesData['categories'])) {
                    $html .= '<ul class="dm-submenu">';
                    foreach ($categoriesData['categories'] as $submenuItem) {
                        $html .= '<li><a href="' . htmlspecialchars(($submenuItem['slug'] ?? '') . ($globalData['page_slug_extension'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
                        $html .= htmlspecialchars(str_replace($categoriesData['title'] ?? '', '', $submenuItem['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $html .= '</a></li>';
                    }
                    $html .= '</ul>';
                }

                $html .= '</li>';
                continue;
            }

            $html .= '<li>';
            $html .= '<a href="' . htmlspecialchars(($menuItem['slug'] ?? '') . ($globalData['page_slug_extension'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
            $html .= htmlspecialchars($menuItem['name'] ?? '', ENT_QUOTES, 'UTF-8');
            $html .= '</a>';

            if (!empty($menuItem['submenu']) && is_array($menuItem['submenu'])) {
                $html .= '<ul class="dm-submenu">';
                foreach ($menuItem['submenu'] as $submenuItem) {
                    $html .= '<li><a href="' . htmlspecialchars(($submenuItem['slug'] ?? '') . ($globalData['page_slug_extension'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
                    $html .= htmlspecialchars($submenuItem['name'] ?? '', ENT_QUOTES, 'UTF-8');
                    $html .= '</a></li>';
                }
                $html .= '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    }
}
