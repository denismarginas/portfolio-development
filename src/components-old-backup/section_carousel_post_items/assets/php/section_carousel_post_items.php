<?php

class SectionCarouselPostItems
{
    public static function render(array $data = []): string
    {
        $posts = $data['posts'] ?? DataService::getJson('index-data-post-projects', 'index') ?? [];
        $layout = $data['layout'] ?? 'standard';

        $content = '';
        $elements = [
            ['component' => 'carousel-post-items-web', 'layout' => null],
            ['component' => 'carousel-post-items-web-device-layouts', 'layout' => 'standard'],
            ['component' => 'carousel-post-items-media', 'layout' => null],
        ];

        $renderedCount = 0;
        foreach ($elements as $element) {
            $targetLayout = $element['layout'];
            if ($targetLayout === null || $targetLayout === $layout) {
                $carouselDirection = ($renderedCount % 2 === 0) ? 'right' : 'left';
                $content .= ComponentRenderer::render_component($element['component'], [
                    'posts' => $posts,
                    'layout' => $layout,
                    'carousel_direction' => $carouselDirection,
                ]);
                $renderedCount++;
            }
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ layout }}', '{{ content }}'],
            [htmlspecialchars($layout), $content],
            $template
        );
    }
}