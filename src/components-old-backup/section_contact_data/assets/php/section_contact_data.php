<?php

class SectionContactData
{
    public static function render(array $data = []): string
    {
        $contactData = $data['contactData'] ?? (DataService::get_personal_data()['contact']['data'] ?? []);
        $urlPath = DataService::get_url_path();

        $imageHtml = '';
        if (!empty($contactData['img'])) {
            $imageHtml .= '<li>';
            $imageHtml .= '<div class="dm-person-circle-card" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s">';
            $imageHtml .= '<span>';
            $imageHtml .= '<div>';
            $imageHtml .= '<span class="circle-background"></span>';
            $imgSrc = $urlPath . 'src/content/img' . ($contactData['img_path'] ?? '') . $contactData['img'];
            $imageHtml .= render_image($imgSrc);
            $imageHtml .= '</div>';
            $imageHtml .= '</span>';
            $imageHtml .= '</div>';
            $imageHtml .= '</li>';
        }

        $textHtml = '';
        $contactTextList = $contactData['text'] ?? [];
        foreach ($contactTextList as $contactTextItem) {
            $textHtml .= '<p data-motion="transition-fade-0 transition-slideInLeft-0">';
            $textHtml .= htmlspecialchars($contactTextItem);
            $textHtml .= '</p>';
        }

        $contactsList = '';
        if (!empty($contactData['contacts'])) {
            $contactsList .= '<ul>';
            $i = 1;
            foreach ($contactData['contacts'] as $contactItem) {
                $duration = 0.6 + (0.1 * $i);
                $contactsList .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="' . $duration . 's">';
                $contactsList .= svg_get('chevron-right');
                $contactsList .= '<span>';
                $contactsList .= '<b>' . htmlspecialchars($contactItem['label'] ?? '') . '</b>';
                if (!empty($contactItem['link'])) {
                    $contactsList .= '<a href="' . htmlspecialchars($contactItem['link']) . '">';
                    $contactsList .= htmlspecialchars($contactItem['value'] ?? '');
                    $contactsList .= '</a>';
                } else {
                    $contactsList .= '<span>' . htmlspecialchars($contactItem['value'] ?? '') . '</span>';
                }
                $contactsList .= '</span>';
                if (!empty($contactItem['warning_text'])) {
                    $contactsList .= '<div class="dm-warning-data">';
                    $contactsList .= '<p>' . htmlspecialchars($contactItem['warning_text']) . '</p>';
                    $contactsList .= '</div>';
                }
                $contactsList .= '</li>';
                $i++;
            }
            $contactsList .= '</ul>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ image_html }}', '{{ title }}', '{{ text_html }}', '{{ contacts_list }}'],
            [$imageHtml, htmlspecialchars($contactData['title'] ?? ''), $textHtml, $contactsList],
            $template
        );
    }
}
