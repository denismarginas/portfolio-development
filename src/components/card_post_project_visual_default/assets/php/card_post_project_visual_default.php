<?php

/**
 * card_post_project_visual_default
 *
 * Fallback visual for a project card: the project's logo (falling back to
 * a thumbnail image) centered on a flat color background. Used for
 * "Miscellaneous Projects", for posts with no matching category, and
 * whenever card_post_project's visual_component is forced to this
 * component.
 *
 * The logo can be either a raster image (data.media.logo.img) or an svg
 * icon (data.media.logo.svg, an icon name rendered through the "svg"
 * component, same convention as card_meta.php / card_taxonomy.php).
 *
 * Expected $data:
 *   'post_current_data' => array   full project post entry (required)
 *   'thumbnail' => string  explicit override path used when the post has
 *                           neither data.media.logo.img nor .svg.
 */
class card_post_project_visual_default
{
    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $settings = is_array($post['settings'] ?? null) ? $post['settings'] : [];

        $logoImg = (string) ($postData['media']['logo']['img'] ?? '');
        $logoSvg = (string) ($postData['media']['logo']['svg'] ?? '');
        $thumbnail = (string) ($data['thumbnail'] ?? '');
        $bgColor = (string) ($settings['appearance']['colors']['canvas_primary'] ?? '');

        $visualImage = '';
        if ($logoImg !== '') {
            $visualImage = PlatformComponentRenderer::render('image', [
                'src' => 'src/content/img/projects/' . $logoImg,
                'alt' => 'Project logo',
                'class' => 'default-visual-image default-visual-logo',
            ]);
        } elseif ($logoSvg !== '') {
            $visualImage = PlatformComponentRenderer::render('svg', [
                'icon' => $logoSvg,
                'class' => 'default-visual-svg default-visual-logo',
            ]);
        } elseif ($thumbnail !== '') {
            $visualImage = PlatformComponentRenderer::render('image', [
                'src' => $thumbnail,
                'alt' => 'Project preview',
                'class' => 'default-visual-image default-visual-thumbnail',
            ]);
        }

        $link = PlatformPathService::post_link((string) ($post['_id'] ?? ''));
        $bgStyle = $bgColor !== ''
            ? ' style="background-color: ' . htmlspecialchars($bgColor, ENT_QUOTES, 'UTF-8') . ';"'
            : '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'bg_style' => $bgStyle,
            'visual_image' => $visualImage,
        ]);
    }
}
