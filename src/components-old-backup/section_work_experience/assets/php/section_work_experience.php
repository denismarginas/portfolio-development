<?php

class SectionWorkExperience
{
    public static function render(array $data = []): string
    {
        $workExperience = $data['workExperience'] ?? (get_data_json('data_content_personal', 'data')["work-experience"] ?? []);
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $urlPath = $GLOBALS['urlPath'];

        $workContent = '';

        if (!empty($workExperience)) {
            $workContent .= '<li class="text">';
            $workContent .= '<h2 data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s" data-delay="0s">';

            if (isset($workExperience["title"])) {
                $workContent .= '<span>' . $workExperience["title"] . '</span>';
            }
            if (isset($workExperience["subtitle"])) {
                $workContent .= '<span>' . $workExperience["subtitle"] . '</span>';
            }

            $workContent .= '</h2>';

            if (isset($workExperience["description"])) {
                $workContent .= '<p data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.4s" data-delay="0.1s">' . $workExperience["description"] . '</p>';
            }

            if (isset($workExperience["buttons"])) {
                $workContent .= '<a data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.4s" href="' . $workExperience["buttons"][0]["button_page_redirect_slug"] . $globalData["page_slug_extension"] . '" data-button="primary">' . $workExperience["buttons"][0]["button_text"] . '</a>';
            }

            $workContent .= '</li>';

            $workContent .= '<li class="projects-previews" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s" data-delay="0s">';

            $images = $workExperience["images"] ?? [];
            $screenImg = $images["screen"] ?? [];
            $cardsListImg = $images["cards"] ?? [];

            if (!empty($cardsListImg)) {
                $workContent .= '<div class="cards-container">';
                $nr = 1;
                $delayCard = 0.30;
                foreach ($cardsListImg as $card) {
                    if (isset($card["img_path"]) && isset($card["img-preview"])) {
                        $imgCard = $urlPath . "src/content/img/" . $card["img_path"] . "/" . $card["img-preview"];
                        $workContent .= render_image($imgCard, false, "card-" . $nr, true, [
                            "data-motion" => "transition-fade-0 transition-slideInLeft-0",
                            "data-duration" => "1s",
                            "data-delay" => $nr * $delayCard . "s"
                        ]);
                    }
                    $nr++;
                }
                if ($nr > 2) {
                    $workContent .= '<div class="bg" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="' . ($nr * $delayCard + 0.1) . 's"></div>';
                }
                $workContent .= '</div>';
            }

            if (isset($screenImg["img_path"]) && isset($screenImg["img-preview"])) {
                $workContent .= '<div class="screen-container" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.1s">';
                $imgPreview = $urlPath . "src/content/img/" . $screenImg["img_path"] . "/" . $screenImg["img-preview"];
                $workContent .= render_image($imgPreview);
                $workContent .= '</div>';
            }

            $workContent .= '</li>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ work_content }}', $workContent, $template);
    }
}
