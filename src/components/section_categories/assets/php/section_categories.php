<?php

class SectionCategories
{
    public static function render(array $data = []): string
    {
        $categoriesData = $data['categoriesData'] ?? DataService::getJson('data-post-projects-terms', 'data') ?? [];
        $globalData = $data['globalData'] ?? DataService::getGlobalSettings();
        $urlPath = DataService::get_url_path();

        $categoriesList = '';
        if (!empty($categoriesData['categories'])) {
            $categoriesList .= '<ul>';
            foreach ($categoriesData['categories'] as $category) {
                $categoriesList .= '<li data-motion="transition-fade-0 transition-slideInTop-0" data-duration="0.5s" data-delay="0.2s">';
                $categoriesList .= '<div class="category-card">';

                if (!empty($categoriesData['overlay-img']) && !empty($categoriesData['overlay-img-path'])) {
                    $categoriesList .= render_image($urlPath . 'content/img/' . $categoriesData['overlay-img-path'] . '/' . $categoriesData['overlay-img']);
                }

                if (!empty($category['img']) && !empty($categoriesData['img_path'])) {
                    $categoriesList .= render_image($urlPath . 'content/img/' . $categoriesData['img_path'] . '/' . $category['img']);
                }

                $categoriesList .= '<span>';
                if (!empty($category['svg-icon'])) {
                    $slug = htmlspecialchars(($category['slug'] ?? '') . ($globalData['page_slug_extension'] ?? ''));
                    $categoriesList .= '<a href="' . $slug . '">';
                    $categoriesList .= svg_get($category['svg-icon']);
                    $categoriesList .= '</a>';
                }
                $categoriesList .= '</span>';

                $categoriesList .= '<div>';
                if (!empty($category['name'])) {
                    $slug = htmlspecialchars(($category['slug'] ?? '') . ($globalData['page_slug_extension'] ?? ''));
                    $categoriesList .= '<a href="' . $slug . '">';
                    $categoriesList .= htmlspecialchars($category['name']);
                    $categoriesList .= '</a>';
                }
                $categoriesList .= '<p>';
                if (!empty($category['short_description'])) {
                    $categoriesList .= '<span>' . htmlspecialchars($category['short_description']) . '</span>';
                }
                $categoriesList .= '</p>';
                $categoriesList .= '</div>';

                $categoriesList .= '</div>';
                $categoriesList .= '</li>';
            }
            $categoriesList .= '</ul>';
        }

        $wavesHtml = ComponentRenderer::render_component('animation_waves', $data);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ title }}', '{{ categories_list }}', '{{ waves_html }}'],
            [htmlspecialchars($categoriesData['title'] ?? ''), $categoriesList, $wavesHtml],
            $template
        );
    }
}
