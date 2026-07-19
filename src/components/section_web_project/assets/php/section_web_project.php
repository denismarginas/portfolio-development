<?php

class SectionWebProject
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $tags = $postData['tags'] ?? [];
        $content = '';

        if (in_array('web', $tags)) {
            $content .= ComponentRenderer::render_component('title', ['text' => 'Web Development']);
            $content .= ComponentRenderer::render_component('gallery_web', [
                'post_current_data' => $postData,
                'gallery_type' => 'web',
            ]);
        }

        if (in_array('content-web', $tags)) {
            $content .= ComponentRenderer::render_component('gallery_web', [
                'post_current_data' => $postData,
                'gallery_type' => 'content',
            ]);
        }

        if (in_array('media-web', $tags)) {
            $content .= ComponentRenderer::render_component('title', ['text' => 'Web Media Content']);
            $content .= ComponentRenderer::render_component('gallery_web', [
                'post_current_data' => $postData,
                'gallery_type' => 'media',
            ]);
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ content }}', $content, $template);
    }
}