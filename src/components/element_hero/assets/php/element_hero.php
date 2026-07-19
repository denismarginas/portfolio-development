<?php

class ElementHero
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');

        $hero_title = $data['title'] ?? "Title";
        $hero_bg_img_path = $data['bgImgPath'] ?? "placeholder";
        $hero_bg_img = $data['bgImg'] ?? "img-placeholder.webp";
        $hero_description = $data['description'] ?? '';
        $layout = $data['layout'] ?? "standard";

        $layoutAttr = '';
        if (isset($layout) && !empty($layout)) {
            $layoutAttr = ' data-layout="' . $layout . '"';
        }

        $bgHtml = '';
        if (isset($hero_bg_img_path) && isset($hero_bg_img)):
            $bgHtml .= '<div class="dm-hero-bg" data-motion="transition-blur-0" data-duration="0.3s" data-delay="0"';
            $bgHtml .= ' style="background-image: url(\'' . $GLOBALS['urlPath'] . "content/img/" . $hero_bg_img_path . "/" . $hero_bg_img . '\');">';
            $bgHtml .= '</div>';
        endif;

        $titleHtml = '';
        if (isset($hero_title) && !empty($hero_title)):
            $titleHtml .= '<h2 data-motion="transition-fade-0 transition-blur-0 transition-slideInBottom-0" data-duration="0.5s" data-delay="0.2s">';
            $titleHtml .= $hero_title;
            $titleHtml .= '</h2>';
        endif;

        $descriptionHtml = '';
        if (isset($hero_description) && !empty($hero_description)):
            $descriptionHtml .= '<p data-motion="transition-fade-0 transition-blur-0 transition-slideInBottom-0" data-duration="0.6s" data-delay="0.25s">';
            $descriptionHtml .= $hero_description;
            $descriptionHtml .= '</p>';
        endif;

        $animationHtml = '';
        if ($layout == "compress-squares"):
            $animationHtml .= render_component('animation_squares');
        endif;

        if ($layout == "compress-waves"):
            $animationHtml .= render_component('animation_waves');
        endif;

        if ($layout == "standard"):
            $animationHtml .= render_component('animation_waves');
        endif;

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ layout_attr }}', '{{ bg_html }}', '{{ title_html }}', '{{ description_html }}', '{{ animation_html }}'],
            [$layoutAttr, $bgHtml, $titleHtml, $descriptionHtml, $animationHtml],
            $template
        );
    }
}
