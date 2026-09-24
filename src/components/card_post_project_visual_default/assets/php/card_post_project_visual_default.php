<?php

require_once __DIR__ . '/parts/project_default_preview.php';

/**
 * Logo on a flat color (canvas_primary) + a preview image shown on hover.
 *
 * Logo: data.media.logo.img -> data.media.logo.svg -> "projects" svg
 *       (each step is used only if its file exists).
 * Preview: see project_default_preview::find().
 */
class card_post_project_visual_default
{
    private const FALLBACK_SVG = 'projects';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $bgColor = (string) ($post['settings']['appearance']['colors']['canvas_primary'] ?? '');
        $preview = project_default_preview::find($postData);

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'link' => htmlspecialchars(PlatformPathService::post_link((string) ($post['_id'] ?? '')), ENT_QUOTES, 'UTF-8'),
            'bg_style' => $bgColor !== '' ? ' style="background-color: ' . htmlspecialchars($bgColor, ENT_QUOTES, 'UTF-8') . ';"' : '',
            'visual_image' => self::logo($postData['media']['logo'] ?? []),
            'preview_image' => $preview !== '' ? self::image($preview, 'default-visual-preview') : '',
        ]);
    }

    private static function logo(mixed $logo): string
    {
        $logo = is_array($logo) ? $logo : [];

        $img = (string) ($logo['img'] ?? '');
        $html = $img !== '' ? self::image('src/content/img/projects/' . ltrim($img, '/'), 'default-visual-image default-visual-logo') : '';

        $svg = (string) ($logo['svg'] ?? '');
        if ($html === '' && $svg !== '') {
            $html = self::svg($svg);
        }

        return $html !== '' ? $html : self::svg(self::FALLBACK_SVG);
    }

    private static function svg(string $icon): string
    {
        return (string) PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => 'default-visual-svg default-visual-logo']);
    }

    /** '' when the file doesn't exist (image component). */
    private static function image(string $src, string $class): string
    {
        return (string) PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => 'Image: ' . pathinfo($src, PATHINFO_FILENAME),
            'class' => $class,
            'lazy' => true,
        ]);
    }
}
