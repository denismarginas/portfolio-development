<?php

class SectionHero
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? null;
        $hero_title = $data['heroTitle'] ?? "Title";
        $hero_bg_img_path = $data['heroBgImgPath'] ?? "placeholder";
        $hero_bg_img = $data['heroBgImg'] ?? "img-placeholder.webp";
        $layout = $data['layout'] ?? "standard";
        $filename = $data['filename'] ?? null;

        $heroData = $filename ? getDataHero($filename) : null;
        $content = render_component('hero', array_merge($data, [
            'layout' => $layout,
            'heroData' => $heroData
        ]));

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ content }}', $content, $template);
    }
}