<?php

class SectionWebDevelopmentDescription
{
    public static function render(array $data = []): string
    {
        $webDevExperience = $data['webDevExperience'] ?? (get_data_json('data_content_personal', 'data')["work-experience"]["web-development-section"] ?? []);
        $urlPath = $GLOBALS['urlPath'];

        $descriptionItems = '';

        if (!empty($webDevExperience)) {
            $textList = $webDevExperience["text-list"] ?? [];
            $imgPath = $webDevExperience["img_path"] ?? '';
            $imgLaptop = $webDevExperience["img-list"]["img-laptop"] ?? '';
            $imgTablet = $webDevExperience["img-list"]["img-tablet"] ?? '';
            $imgPhone = $webDevExperience["img-list"]["img-phone"] ?? '';

            if (!empty($textList)) {
                $middle = (int)(count($textList) / 2);

                foreach ($textList as $key => $item) {
                    if ($key == $middle) {
                        $descriptionItems .= '<li><section class="dm-web-responsive" data-motion="transition-fade-0">';

                        if (!empty($imgPath) && !empty($imgLaptop)) {
                            $descriptionItems .= render_image($urlPath . "content/img/" . $imgPath . "/" . $imgLaptop, false, "dm-laptop-responsive");
                        }
                        if (!empty($imgPath) && !empty($imgTablet)) {
                            $descriptionItems .= render_image($urlPath . "content/img/" . $imgPath . "/" . $imgTablet, false, "dm-tablet-responsive");
                        }
                        if (!empty($imgPath) && !empty($imgPhone)) {
                            $descriptionItems .= render_image($urlPath . "content/img/" . $imgPath . "/" . $imgPhone, false, "dm-phone-responsive");
                        }

                        $descriptionItems .= '</section></li>';
                    }

                    if ($key == 0 || $key == $middle) {
                        $descriptionItems .= '<li>';
                    }

                    $descriptionItems .= '<div class="dm-description-item">';

                    if (isset($item["title"])) {
                        $descriptionItems .= '<h3 data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s" data-delay="' . ($key * 0.05) . 's">' . $item["title"] . '</h3>';
                    }

                    if (isset($item["description"])) {
                        $descriptionItems .= '<p data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s" data-delay="' . ($key * 0.1) . 's">' . $item["description"] . '</p>';
                    }

                    $descriptionItems .= '</div>';

                    if ($key == $middle || $key == count($textList) - 1) {
                        $descriptionItems .= '</li>';
                    }
                }
            }
        }

        $animation = render_component('animation_blurred_lines', []);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ description_items }}', '{{ animation }}'],
            [$descriptionItems, $animation],
            $template
        );
    }
}
