<?php

require_once __DIR__ . '/post_short_info_post.php';

/**
 * Post logo on the canvas_primary color with a faded preview image behind it
 * (the old section_post ".post-image.post-logo.bg-thumbnail").
 *
 * data:
 *   post_id         string  optional, show another post (default: current post)
 *   show_preview    bool    faded thumbnail behind the logo (default true)
 *   class           string  optional extra class
 *
 * Logo:    data.media.logo.img -> data.media.logo.svg -> "projects" svg
 * Preview: only data.media.thumbnail (relative to src/content/img/projects/), when the file exists
 */
class post_short_info_visual
{
    private const PROJECTS_IMG_DIR = 'src/content/img/projects/';
    private const FALLBACK_SVG = 'projects';

    public static function render(array $data = []): string
    {
        $post = post_short_info_post::resolve($data);
        if (empty($post)) return '';

        $postData = post_short_info_post::post_data($post);
        $title = (string) ($postData['seo']['title'] ?? '');

        $logo = self::logo($postData['media']['logo'] ?? [], $title);

        $preview = '';
        if (post_short_info_post::flag($data, 'show_preview', true)) {
            $previewPath = self::thumbnail($postData['media']['thumbnail'] ?? '');
            if ($previewPath !== '') {
                $preview = (string) PlatformComponentRenderer::render('image', [
                    'src' => $previewPath,
                    'alt' => 'Post Thumbnail - ' . $title,
                    'class' => 'post-short-info-visual-preview',
                    'lazy' => true,
                ]);
            }
        }

        $classes = [$logo['type'] === 'svg' ? 'post-short-info-visual-svg' : 'post-short-info-visual-img'];
        if ($preview !== '') $classes[] = 'post-short-info-visual-has-preview';
        $extra = trim((string) ($data['class'] ?? ''));
        if ($extra !== '') $classes[] = $extra;

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => ' ' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8'),
            'preview_image' => $preview,
            'logo' => $logo['html'],
        ]);
    }

    /** @return array{type:string, html:string} */
    private static function logo(mixed $logo, string $title): array
    {
        $logo = is_array($logo) ? $logo : [];

        $img = trim((string) ($logo['img'] ?? ''));
        if ($img !== '') {
            $src = str_starts_with($img, 'src/') ? $img : self::PROJECTS_IMG_DIR . ltrim($img, '/');
            $html = (string) PlatformComponentRenderer::render('image', [
                'src' => $src,
                'alt' => 'Post Logo - ' . $title,
                'class' => 'post-short-info-visual-logo',
            ]);
            if ($html !== '') return ['type' => 'img', 'html' => $html];
        }

        $svg = trim((string) ($logo['svg'] ?? ''));
        $html = $svg !== '' ? self::svg($svg) : '';
        if ($html === '') $html = self::svg(self::FALLBACK_SVG);

        return ['type' => 'svg', 'html' => $html];
    }

    /** data.media.thumbnail -> project relative path, '' when not set. */
    private static function thumbnail(mixed $thumbnail): string
    {
        $thumbnail = is_string($thumbnail) ? trim($thumbnail) : '';
        if ($thumbnail === '') return '';
        return str_starts_with($thumbnail, 'src/') ? $thumbnail : self::PROJECTS_IMG_DIR . ltrim($thumbnail, '/');
    }

    private static function svg(string $icon): string
    {
        return (string) PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => 'post-short-info-visual-logo']);
    }
}
