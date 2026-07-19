<?php

class ExperienceCategories
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $categories = $data['categories'] ?? get_data_json('data_content_personal', 'data')["cards_categories"] ?? [];

        if (empty($categories)) {
            return '';
        }

        $categoriesList = '';
        $i = 1;
        foreach ($categories as $experience_category) {
            $experience_category_href = "#";
            if ($experience_category["type"] == "page") {
                $experience_category_href = $experience_category["page"] . $jsonGlobalData["page_slug_extension"];
            } else {
                $experience_category_href = $experience_category["section"];
            }

            $categoriesList .= '<li class="dm-experience-category" data-motion="transition-fade-0" data-duration="0.7s" data-delay="' . ($i * 0.1) . 's">';
            $categoriesList .= '<div>';
            $categoriesList .= '<a href="' . htmlspecialchars($experience_category_href) . '">';
            $categoriesList .= '<div>';
            $categoriesList .= svg_get($experience_category["svg"]);
            $categoriesList .= '</div>';
            $categoriesList .= '<span>' . htmlspecialchars($experience_category["name"]) . '</span>';
            $categoriesList .= render_image($GLOBALS['urlPath'] . "content/img" . $experience_category["background_img_path"] . $experience_category["background_img"]);
            $categoriesList .= '</a>';
            $categoriesList .= '</div>';
            $categoriesList .= '</li>';
            $i++;
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ categories_list }}', $categoriesList, $template);
    }
}
