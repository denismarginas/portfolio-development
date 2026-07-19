<?php

class Slider
{
    public static function render(array $data): string
    {
        $slides = $data['slides'] ?? [];
        $sliderClass = $data['class'] ?? 'component-slider';

        if (!is_array($slides) || empty($slides)) {
            return '';
        }

        $items = array_map(function ($slide) {
            $slideContent = '';
            if (is_array($slide)) {
                $imageHtml = '';
                if (!empty($slide['image'])) {
                    $imageHtml = render_component('image', [
                        'src' => $slide['image'],
                        'alt' => $slide['alt'] ?? '',
                        'class' => 'slider-image',
                        'lazy' => true,
                    ]);
                    $imageHtml = '<div class="slider-item-image">' . $imageHtml . '</div>';
                }

                $slideContent = $slide['content'] ?? '';
                if (!empty($slide['number_text'])) {
                    $slideContent = $slide['number_text'] . $slideContent;
                }
            } elseif (is_string($slide)) {
                $slideContent = $slide;
            }

            return sprintf(
                '<div class="slider-item">%s<div class="slider-item-content">%s</div></div>',
                $imageHtml ?? '',
                $slideContent
            );
        }, $slides);

        return self::render_template([
            'slider_class' => htmlspecialchars($sliderClass, ENT_QUOTES, 'UTF-8'),
            'slider_items' => implode(PHP_EOL, $items),
        ]);
    }

    protected static function render_template(array $data): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }
        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }
        return $html;
    }
}
