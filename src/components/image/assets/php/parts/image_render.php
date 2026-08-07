<?php

trait image_render
{
    public static function render(array $data = []): string
    {
        $src = (string)($data['src'] ?? $data['path'] ?? '');
        if ($src === '') return '';

        $width = '';
        $height = '';
        if (!PlatformUrlService::is_external_url($src)) {
            $absolute = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT . '/' . $src : $src;
            if (!file_exists($absolute)) return '';
            $src = PlatformPathService::asset_relative_prefix() . $src;
            $size = self::get_sizes($absolute);
            if ($size && !isset($data['width']) && !isset($data['height'])) {
                $width = ' width="' . $size[0] . '"';
                $height = ' height="' . $size[1] . '"';
            }
        }
        if (isset($data['width'])) {
            $width = ' width="' . htmlspecialchars((string)$data['width'], ENT_QUOTES, 'UTF-8') . '"';
        }
        if (isset($data['height'])) {
            $height = ' height="' . htmlspecialchars((string)$data['height'], ENT_QUOTES, 'UTF-8') . '"';
        }

        $lazy = !empty($data['lazy']) ? ' loading="lazy"' : '';

        $title = (string)($data['title'] ?? '');
        $titleAttr = $title !== '' ? ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template.html', [
            'class' => htmlspecialchars((string)($data['class'] ?? 'responsive-image')),
            'src' => htmlspecialchars($src),
            'alt' => htmlspecialchars((string)($data['alt'] ?? '')),
            'title_attr' => $titleAttr,
            'lazy' => $lazy,
            'width' => $width,
            'height' => $height,
        ]);
    }
}
