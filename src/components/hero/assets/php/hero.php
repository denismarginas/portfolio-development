<?php

class Hero
{
    public static function render(array $data = []): string
    {
        $title = $data['title'] ?? 'Title';
        $description = $data['description'] ?? '';
        $background = $data['background'] ?? '';
        $layout = $data['layout'] ?? 'standard';
        $classes = $data['class'] ?? 'dm-hero-block';

        $style = '';
        if (!empty($background)) {
            $style = ' style="background-image: url(\'' . htmlspecialchars($background, ENT_QUOTES, 'UTF-8') . '\')"';
        }

        $descriptionHtml = '';
        if (!empty($description)) {
            $descriptionHtml = '<p data-motion="transition-fade-0 transition-blur-0 transition-slideInBottom-0" data-duration="0.6s" data-delay="0.25s">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ classes }}', '{{ layout }}', '{{ style }}', '{{ title }}', '{{ description_html }}'],
            [htmlspecialchars($classes, ENT_QUOTES, 'UTF-8'), htmlspecialchars($layout, ENT_QUOTES, 'UTF-8'), $style, htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), $descriptionHtml],
            $template
        );
    }
}
