<?php

class button
{
    public static function render(array $data = []): string
    {
        $text = (string) ($data['text'] ?? '');
        $link = self::resolve_link($data);
        if ($text === '' || $link === '') return '';

        $class = (string) ($data['class'] ?? 'btn btn-primary');

        $svg = '';
        $icon = (string) ($data['svg'] ?? '');
        if ($icon !== '') {
            $svg = PlatformComponentRenderer::render('svg', [
                'icon' => $icon,
                'class' => 'btn-icon',
            ]);
        }

        $attrs = '';
        $target = (string) ($data['target'] ?? '');
        if ($target !== '') {
            $attrs .= ' target="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"';
        }
        $rel = (string) ($data['rel'] ?? '');
        if ($rel !== '') {
            $attrs .= ' rel="' . htmlspecialchars($rel, ENT_QUOTES, 'UTF-8') . '"';
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'class' => htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
            'href' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'text' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
            'svg' => $svg,
            'attrs' => $attrs,
        ]);
    }

    protected static function resolve_link(array $data): string
    {
        $link = $data['link'] ?? '';

        if (is_string($link) && $link !== '') {
            return $link;
        }

        if (is_array($link)) {
            $postId = (string) ($link['post_id'] ?? $link['slug'] ?? '');
            if ($postId !== '') {
                return PlatformPathService::post_link($postId);
            }
        }

        return '';
    }
}