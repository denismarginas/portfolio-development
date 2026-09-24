<?php

require_once __DIR__ . '/parts/project_media_finder.php';

/**
 * Photo + logo card for "Visual Media Projects".
 *
 * $data options:
 *   post_current_data  array   project post (required)
 *   texture            string  overlay texture image, "" to disable
 *                              (default "design-elements/overlay-texture-paper.webp")
 *   lazy               bool    lazy-load images (default true)
 *
 * Photo: see project_media_finder::photo(). Logo: data.media.logo.img
 * (image) or data.media.logo.svg (svg icon). Nothing found -> ''.
 */
class card_post_project_visual_media
{
    private const DEFAULT_TEXTURE = 'design-elements/overlay-texture-paper.webp';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $lazy = !array_key_exists('lazy', $data) || (bool) $data['lazy'];

        $photo = self::image(project_media_finder::photo($postData), 'media-photo', $lazy);
        $logo = self::logo($postData['media']['logo'] ?? [], $lazy);
        if ($photo === '' && $logo === '') {
            return '';
        }

        $colors = $post['settings']['appearance']['colors'] ?? [];
        $color = self::color($colors['primary'] ?? '');
        $logoColor = self::color($colors['canvas_primary'] ?? '');
        $bgStyle = self::bg_style($color);
        $texture = (string) ($data['texture'] ?? self::DEFAULT_TEXTURE);
        $link = PlatformPathService::post_link((string) ($post['_id'] ?? '')) . '#visualmedia';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'color_style' => $color !== '' ? ' style="--primary-color-post: ' . $color . ';"' : '',
            'bg_style' => $bgStyle,
            'photo' => $photo,
            'logo' => $logo !== '' ? '<div class="logo"' . self::bg_style($logoColor) . '>' . $logo . '</div>' : '',
            'texture' => self::image($texture, 'texture', $lazy),
        ]);
    }

    private static function color(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function bg_style(string $color): string
    {
        return $color !== '' ? ' style="background-color: ' . $color . ';"' : '';
    }

    private static function logo(mixed $logo, bool $lazy): string
    {
        if (!is_array($logo)) {
            return '';
        }

        $img = (string) ($logo['img'] ?? '');
        if ($img !== '') {
            return self::image('src/content/img/projects/' . ltrim($img, '/'), 'logo-image', $lazy);
        }

        $svg = (string) ($logo['svg'] ?? '');
        return $svg !== ''
            ? PlatformComponentRenderer::render('svg', ['icon' => $svg, 'class' => 'logo-svg'])
            : '';
    }

    /** Renders through the image component ('' when the file is missing). */
    private static function image(string $src, string $class, bool $lazy): string
    {
        if ($src === '') {
            return '';
        }

        return PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => 'Image: ' . pathinfo($src, PATHINFO_FILENAME),
            'class' => $class,
            'lazy' => $lazy,
        ]);
    }
}
