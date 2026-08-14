<?php

class SectionResumeData
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $resumeData = $data['resumeData'] ?? (get_data_json('data_content_personal', 'data')["contact"]["resume"] ?? []);
        $urlPath = $GLOBALS['urlPath'];

        if (empty($resumeData)) {
            return '';
        }

        $resumeCards = '';
        $cvList = $resumeData["cv-list"] ?? [];
        $i = 1;
        foreach ($cvList as $cvItem) {
            $resumeCards .= '<li class="resume-card" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.' . (1 + $i) . 's">';
            $resumeCards .= '<div>';
            if (isset($cvItem['thumbnail'])) {
                $resumeCards .= '<span>';
                if (isset($cvItem['image'])) {
                    $resumeCards .= '<div class="cv-image-view">' . render_image($urlPath . $cvItem['image'], 1) . '</div>';
                }
                $resumeCards .= render_image($urlPath . $cvItem['thumbnail']);
                $resumeCards .= svg_get('resume');
                $resumeCards .= '</span>';
            }
            $resumeCards .= '<div>';
            if (isset($cvItem['pdf']) && isset($cvItem['name'])) {
                $resumeCards .= '<a href="' . $urlPath . $cvItem['pdf'] . '" target="_blank">' . $cvItem['name'] . '</a>';
            }
            if (isset($cvItem['description'])) {
                $resumeCards .= '<span>' . $cvItem['description'] . '</span>';
            }
            if (isset($cvItem['date'])) {
                $resumeCards .= '<span>' . $cvItem['date'] . '</span>';
            }
            if (isset($cvItem['pdf'])) {
                $resumeCards .= '<button data-button="primary" class="downloadButton" data-url="' . $urlPath . $cvItem['pdf'] . '">' . $resumeData["download-button-text"] . '</button>';
            }
            $resumeCards .= '</div></div></li>';
            $i++;
        }

        $resumeImage = '';
        if (isset($resumeData["img_path"]) && isset($resumeData["img"])) {
            $resumeImage .= render_image($urlPath . "src/content/img" . $resumeData["img_path"] . $resumeData["img"], false, false, true, ['data-motion' => 'transition-fade-0 transition-slideInLeft-0']);
        }

        $textList = '';
        $textData = $resumeData["text-list"] ?? [];
        $j = 1;
        foreach ($textData as $textItem) {
            $textList .= '<p data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.' . (1 + $j) . 's">' . $textItem . '</p>';
            $j++;
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ resume_cards }}', '{{ resume_image }}', '{{ text_list }}'],
            [$resumeCards, $resumeImage, $textList],
            $template
        );
    }
}
