<?php

class Header
{
    public static function render(array $data = []): string
    {
        $jsonMenuData = get_data_json('data_menu', 'data');
        $jsonGlobalData = get_data_json('data_global_settings', 'data');
        $jsonCategoriesData = get_data_json('data_post_projects_terms', 'data');

        $headerBlocks = $jsonGlobalData['theme_active']['header'] ?? [];
        $logoHtml = '';
        $menuHtml = '';

        if (isset($headerBlocks['block_1']) && $headerBlocks['block_1'] === 'logo') {
            $logoHtml = HeaderLogo::render([
                'frontPageSlug' => $jsonGlobalData['front_page']['slug'] . $jsonGlobalData['page_slug_extension'],
                'logoPath' => $GLOBALS['urlPath'] . 'src/content/img' . $jsonGlobalData['logo']['path'] . $jsonGlobalData['logo']['img'],
                'siteIdentity' => $jsonGlobalData['site_identity'],
                'primaryTitle' => $jsonGlobalData['logo']['primary_title'],
                'secondaryTitle' => $jsonGlobalData['logo']['secondary_title'],
            ]);
        }

        if (isset($headerBlocks['block_2']) && $headerBlocks['block_2'] === 'menu') {
            $menuHtml = HeaderMenu::render([
                'menuData' => $jsonMenuData,
                'categoriesData' => $jsonCategoriesData,
                'globalData' => $jsonGlobalData,
            ]);
        }

        return self::render_template([
            'logo_html' => $logoHtml,
            'menu_html' => $menuHtml,
            'page_heading' => !empty($data['page_heading']) ? '<h1 class="page-heading">' . htmlspecialchars($data['page_heading']) . '</h1>' : '',
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
