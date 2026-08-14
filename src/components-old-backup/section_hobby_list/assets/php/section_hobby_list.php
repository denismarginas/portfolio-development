<?php

class SectionHobbyList
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $jsonHobbyData = $data['hobbyData'] ?? get_data_json('data_content_personal', 'data')["hobby"];

        $title = '';
        if (isset($jsonHobbyData["title"])) {
            $title .= '<h4 class="name" data-motion="transition-fade-0" data-duration="0.4s">' . $jsonHobbyData["title"] . '</h4>';
        }

        $listItems = '';
        if (isset($jsonHobbyData["list"])) {
            $listItems .= '<ul>';
            $i = 1;
            foreach ($jsonHobbyData["list"] as $item) {
                $style = '';
                if (isset($jsonHobbyData["img_path"]) && isset($item["img"])) {
                    $style = ' style="background-image: url(\'' . $GLOBALS['urlPath'] . 'src/content/img/' . $jsonHobbyData["img_path"] . '/' . $item["img"] . '\');"';
                }
                $listItems .= '<li class="dm-hobby-item"' . $style . ' data-motion="transition-fade-0" data-duration="0.4s" data-delay="' . ($i * 0.05) . 's">';
                if (isset($item["name"])) {
                    $listItems .= '<p class="name" data-motion="transition-fade-0" data-duration="0.3s" data-delay="0.05s">' . $item["name"] . '</p>';
                }
                if (isset($item["icon-svg"])) {
                    ob_start();
                    svg_render($item["icon-svg"]);
                    $listItems .= '<div class="icon" data-motion="transition-fade-0" data-duration="0.4s" data-delay="0.1s">' . ob_get_clean() . '</div>';
                }
                $listItems .= '</li>';
                $i++;
            }
            $listItems .= '</ul>';
        }

        $animation = render_component('animation_blurred_lines', $data);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ title }}', '{{ list_items }}', '{{ animation }}'],
            [$title, $listItems, $animation],
            $template
        );
    }
}
