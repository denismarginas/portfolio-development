<?php

class SliderWithImages
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $pathDir = $data['path'] ?? '';
        $urlPath = DataService::get_url_path();

        if (empty($pathDir)) {
            return '';
        }

        $postType = $postData['post_type'] ?? 'projects';
        $mediaPath = $postData['media_path'] ?? '';
        $slidesPath = $urlPath . 'content/img/' . $postType . '/' . $mediaPath . '/' . $pathDir;

        $srcCurrent = realpath(__DIR__ . '/../../../../content/img/' . $postType . '/' . $mediaPath . '/' . $pathDir);
        if (!is_dir($srcCurrent)) {
            return '';
        }

        $images = array_diff(scandir($srcCurrent), ['.', '..']);
        $rendered = [];

        foreach ($images as $img) {
            $rendered[] = ComponentRenderer::render_component('image', [
                'src' => $slidesPath . $img,
                'popup' => true,
            ]);
        }

        if (empty($rendered)) {
            return '';
        }

        $content = ComponentRenderer::render_component('slider', [
            'slides' => array_map(function ($html) {
                return ['content' => $html];
            }, $rendered),
            'show_numbers' => true,
            'show_arrows' => true,
            'show_dots' => false,
        ]);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ content }}', $content, $template);
    }
}