<?php

class SectionExperienceKnowledge
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $jsonDataExperience = $data['experienceData'] ?? get_data_json('data_content_personal', 'data')["experience"];
        $layout = $data['layout'] ?? null;

        $images = $jsonDataExperience["images"];

        $personImage = '';
        if (isset($images["portrait"])) {
            $personImage = '<div class="person-image" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s">';
            $personImage .= render_image($GLOBALS['urlPath'] . "src/content/img" . $images["portrait"]["img_path"] . $images["portrait"]["img"]);
            $personImage .= '<div class="graphic-element-1">';
            ob_start();
            svg_render('dots-graphic');
            $personImage .= ob_get_clean();
            $personImage .= '</div>';
            $personImage .= '</div>';
        }

        $title = '';
        if (isset($jsonDataExperience["title"])) {
            $title = '<h2 data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.5s">' . $jsonDataExperience["title"] . '</h2>';
        }

        $textItems = '';
        if (isset($jsonDataExperience["knowledge_lists_text"]["text_items"])) {
            $experience_text = $jsonDataExperience["knowledge_lists_text"]["text_items"];
            $i = 1;
            foreach ($experience_text as $experience_text_item) {
                $textItems .= '<p data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.6s" data-delay="' . ($i * 0.03) . 's">' . $experience_text_item . '</p>';
                $i++;
            }
        }

        $knowledgeList = '';
        if (isset($jsonDataExperience["knowledge_lists_text"]["list_items"])) {
            $knowledge_categories = $jsonDataExperience["knowledge_lists_text"]["list_items"];
            $knowledgeList = '<div class="list">';
            $j = 1;
            foreach ($knowledge_categories as $category) {
                $knowledgeList .= '<div class="list-section">';
                $knowledgeList .= '<p class="subtitle" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.6s" data-delay="' . ($j * 0.01) . 's">' . $category['category'] . '</p>';
                $knowledgeList .= '<ul>';
                $n = 1;
                foreach ($category['items'] as $item) {
                    $knowledgeList .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.6s" data-delay="' . (($j * 0.01) + ($n * 0.02)) . 's">' . $item . '</li>';
                    $n++;
                }
                $knowledgeList .= '</ul>';
                $knowledgeList .= '</div>';
                $j++;
            }
            $knowledgeList .= '</div>';
        }

        $knowledgeListIcons = '';
        if (isset($jsonDataExperience["knowledge_lists_items"])) {
            $knowledgeListIcons = render_component('knowledge_list_icons', $data);
        }

        $buttons = '';
        if (isset($layout) && ($layout == "standard")) {
            if (isset($jsonDataExperience["buttons"])) {
                $buttons = '<div class="buttons">';
                foreach ($jsonDataExperience["buttons"] as $button) {
                    $buttons .= '<a data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.4s" href="' . $button["button_page_redirect_slug"] . $jsonGlobalData["page_slug_extension"] . '" data-button="primary">';
                    ob_start();
                    svg_render($button["button_icon_svg"]);
                    $buttons .= ob_get_clean();
                    $buttons .= '<span>' . $button["button_text"] . '</span>';
                    $buttons .= '</a>';
                }
                $buttons .= '</div>';
            }
        }

        $experienceContainer = '';
        if (isset($jsonDataExperience["text"])) {
            $experienceContainer = '<div class="experience-container">';
            $experienceContainer .= '<div class="text">';
            if (is_array($jsonDataExperience["text"])) {
                $i = 1;
                foreach ($jsonDataExperience["text"] as $textItem) {
                    $experienceContainer .= '<p data-motion="transition-fade-0" data-duration="1.2s" data-delay="' . ($i * 0.06) . 's">' . htmlspecialchars($textItem) . '</p>';
                    $i++;
                }
            } else {
                $experienceContainer .= '<p data-motion="transition-fade-0" data-duration="0.8s" data-delay="0.1s">' . htmlspecialchars($jsonDataExperience["text"]) . '</p>';
            }
            $experienceContainer .= '</div>';
            if (isset($images["banner"])) {
                $experienceContainer .= '<div class="banner-image" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s">';
                $experienceContainer .= render_image($GLOBALS['urlPath'] . "src/content/img" . $images["banner"]["img_path"] . $images["banner"]["img"], true);
                $experienceContainer .= '</div>';
            }
            $experienceContainer .= '</div>';
        }

        return self::render_template([
            'person_image' => $personImage,
            'title' => $title,
            'text_items' => $textItems,
            'knowledge_list' => $knowledgeList,
            'knowledge_list_icons' => $knowledgeListIcons,
            'buttons' => $buttons,
            'experience_container' => $experienceContainer,
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
