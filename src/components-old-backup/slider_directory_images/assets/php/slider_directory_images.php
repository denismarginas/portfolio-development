<?php

class SliderDirectoryImages
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $pathDir = $data['img_array_path_dir'] ?? $data['path'] ?? '';
        $urlPath = DataService::get_url_path();

        if (empty($pathDir)) {
            return '';
        }

        $baseImgDir = $data['global_img_path'] ?? '';
        if (empty($baseImgDir)) {
            return '';
        }

        $mediaPath = '';
        if (isset($postData['data']['media']['path'])) {
            $mediaPath = $postData['data']['media']['path'];
        } elseif (isset($postData['media_path'])) {
            $mediaPath = $postData['media_path'];
        }
        if (empty($mediaPath)) {
            return '';
        }

        $fullDir = ENGINE_PROJECT_ROOT . '/' . $baseImgDir . '/' . $mediaPath . '/' . $pathDir;
        $srcDir = realpath($fullDir);
        if ($srcDir === false || !is_dir($srcDir)) {
            return '<span>Path Not Found</span>';
        }

        $slidesPath = rtrim($urlPath, '/') . '/' . $baseImgDir . '/' . $mediaPath . '/' . $pathDir . '/';
        $images = array_diff(scandir($srcDir), ['.', '..']);
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
