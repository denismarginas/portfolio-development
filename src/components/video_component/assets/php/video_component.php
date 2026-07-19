<?php

class Video
{
    public static function render(array $data = []): string
    {
        $videoData = $data['videoData'] ?? [];
        $content = render_component('video', $videoData);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ content }}', $content, $template);
    }
}