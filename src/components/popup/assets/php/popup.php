<?php

class popup
{
    public static function render(array $data = []): string
    {
        // Static usage: render a trigger that opens given content in a popup
        $contentHtml = (string) ($data['content'] ?? $data['content_html'] ?? '');
        $triggerHtml = (string) ($data['trigger'] ?? $data['trigger_html'] ?? '');
        if ($contentHtml === '' && $triggerHtml === '') return '';

        $gallery = is_array($data['items'] ?? null) ? $data['items'] : [];
        $inner = '';

        if (!empty($gallery)) {
            // Gallery mode: items = array of html strings or ['src'=>..,'alt'=>..] images
            $slides = '';
            foreach ($gallery as $item) {
                $slides .= '<div class="popup-slide">' . (is_array($item)
                    ? PlatformComponentRenderer::render('image', ['src' => $item['src'] ?? '', 'alt' => $item['alt'] ?? ''])
                    : (string) $item) . '</div>';
            }
            $inner = '<div class="popup-gallery" data-popup-gallery>' . $slides . '</div>';
        } else {
            $inner = $contentHtml;
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/parts/trigger.html', [
            'trigger' => $triggerHtml,
            'content' => $inner,
            'class' => htmlspecialchars((string) ($data['class'] ?? ''), ENT_QUOTES, 'UTF-8'),
        ]);
    }
}