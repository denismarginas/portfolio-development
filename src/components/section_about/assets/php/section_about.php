<?php

class SectionAbout
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? DataService::getGlobalSettings();
        $aboutData = $data['aboutData'] ?? (DataService::get_personal_data()['about'] ?? []);
        $layout = $data['layout'] ?? 'standard';
        $urlPath = DataService::get_url_path();

        $imageHtml = '';
        $images = $aboutData['images'] ?? [];
        $renderImageData = $images['compress'] ?? null;
        if ($layout === 'standard') {
            $renderImageData = $images['standard'] ?? null;
        }

        if (!empty($renderImageData)) {
            $imageHtml .= '<div data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s">';
            $imgSrc = $urlPath . 'content/img' . ($renderImageData['img_path'] ?? '') . ($renderImageData['img'] ?? '');
            $imageHtml .= render_image($imgSrc);
            $imageHtml .= svg_get('background-shape-1');
            $imageHtml .= '<span data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s" data-delay="0.5s"></span>';
            $imageHtml .= '</div>';
        }

        $textHtml = '';
        $aboutTextList = $aboutData['text'] ?? [];
        $i = 1;
        foreach ($aboutTextList as $aboutTextItem) {
            $textHtml .= '<p data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.7s">';
            $textHtml .= execute_php_in_string($aboutTextItem);
            $textHtml .= '</p>';
            if ($layout === 'compress') {
                $buttonHtml = '';
                $btn = $aboutData['buttons'][0] ?? [];
                $link = ($btn['button_page_redirect_slug'] ?? '') . ($globalData['page_slug_extension'] ?? '');
                $buttonHtml .= '<a data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.4s"';
                $buttonHtml .= ' href="' . htmlspecialchars($link) . '"';
                $buttonHtml .= ' data-button="primary">';
                $buttonHtml .= htmlspecialchars($btn['button_text'] ?? '');
                $buttonHtml .= '</a>';
                break;
            }
            $i++;
        }

        $buttonHtml = $buttonHtml ?? '';

        $categoriesHtml = '';
        if ($layout === 'standard') {
            $categoriesHtml .= ComponentRenderer::render_component('experience_categories', $data);
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ layout }}', '{{ image_html }}', '{{ title }}', '{{ text_html }}', '{{ button_html }}', '{{ categories_html }}'],
            [htmlspecialchars($layout), $imageHtml, htmlspecialchars($aboutData['title'] ?? ''), $textHtml, $buttonHtml, $categoriesHtml],
            $template
        );
    }
}
