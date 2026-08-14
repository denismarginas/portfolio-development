<?php

class Video 
{
    private static $templatePath = __DIR__ . '/../html/template.html';

    public static function render(array $data): string 
    {
        if (!file_exists(self::$templatePath)) {
            return "";
        }

        $html = file_get_contents(self::$templatePath);

        $thumbnailSection = '';
        if (!empty($data['thumbnail'])) {
            $bgStyle = !empty($data['thumbnail_bg'])
                ? 'style="background-image: url(\'' . htmlspecialchars($data['thumbnail_bg']) . '\')"'
                : '';

            $thumbnailImage = function_exists('renderImage')
                ? render_image($data['thumbnail'], false, '', true, ['alt' => $data['thumbnail_alt'] ?? 'Video thumbnail'])
                : '<img src="' . htmlspecialchars($data['thumbnail']) . '" alt="Video thumbnail">';

            $playSVG = class_exists('SVG') ? svg_get('play') : '';
            $pauseSVG = class_exists('SVG') ? svg_get('pause') : '';

            $thumbnailSection = '<div class="thumbnail" ' . $bgStyle . '>' .
                $thumbnailImage .
                '<div class="show-play" style="display:flex;">' . $playSVG . '</div>' .
                '<div class="show-pause" style="display:none;">' . $pauseSVG . '</div>' .
                '</div>';
        }

        $html = str_replace([
            '{{ src }}',
            '{{ thumbnail_section }}'
        ], [htmlspecialchars($data['src'] ?? ''), $thumbnailSection], $html);

        return $html;
    }
}