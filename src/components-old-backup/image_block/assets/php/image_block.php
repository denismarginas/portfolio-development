<?php

class ImageBlock
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $imagePath = $data['image_path'] ?? '';
        $imageFile = $data['image_file'] ?? '';
        $urlPath = DataService::get_url_path();

        $src = '';
        if (!empty($imagePath) && !empty($imageFile)) {
            $postType = $postData['post_type'] ?? 'projects';
            $mediaPath = $postData['media_path'] ?? '';
            $src = $urlPath . 'src/content/img/' . $postType . '/' . $mediaPath . '/' . $imagePath . $imageFile;
        } elseif (!empty($data['src'])) {
            $src = $data['src'];
        }

        if (empty($src)) {
            return '';
        }

        $content = ComponentRenderer::render_component('image', ['src' => $src, 'popup' => true]);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ content }}', $content, $template);
    }
}