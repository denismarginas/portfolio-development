<?php

class Carousel
{
    public function render(array $data = []): string
    {
        $items = $data['items'] ?? [];
        $showNumbers = !empty($data['show_numbers']);

        if (!is_array($items) || empty($items)) {
            return '';
        }

        $sliderElements = '';
        foreach ($items as $index => $item) {
            $numberText = $showNumbers ? '<div class="number-text">' . ($index + 1) . ' / ' . count($items) . '</div>' : '';
            $sliderElements .= '<div class="slider-element">' . $numberText . ($item['content'] ?? '') . '</div>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace('{{ slider_elements }}', $sliderElements, $template);
    }
}
