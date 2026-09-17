<?php

/**
 * card_post_project_visual_media
 *
 * PLACEHOLDER / structure only. Visual for a project card when the post
 * belongs to the "Visual Media Projects" category. For now this renders a
 * single cover image (with a play icon overlay, for when the project is a
 * video) - the real image-folder convention (single cover vs. gallery vs.
 * video embed) still needs to be defined, same way "web/overview/" was
 * defined for the website visual.
 *
 * Expected $data:
 *   'post_current_data' => array   full project post entry (required)
 *   'cover_image'  => string  explicit override path for the cover image.
 *                              When omitted, falls back to
 *                              "src/content/img/projects/<media.path>/media/overview/media_overview.webp".
 *   'is_video'     => bool    when true, renders the play-icon overlay.
 *                              Default: false.
 */
class card_post_project_visual_media
{
    private const DEFAULT_COVER_RELATIVE = 'media/overview/media_overview.webp';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $mediaPath = (string) ($postData['media']['path'] ?? '');

        $coverImage = (string) ($data['cover_image'] ?? '');
        if ($coverImage === '' && $mediaPath !== '') {
            $coverImage = 'src/content/img/projects/' . $mediaPath . '/' . self::DEFAULT_COVER_RELATIVE;
        }

        $link = PlatformPathService::post_link((string) ($post['_id'] ?? '')) . '#visualmedia';

        $coverImageHtml = $coverImage !== ''
            ? PlatformComponentRenderer::render('image', [
                'src' => $coverImage,
                'alt' => 'Project media preview',
                'class' => 'media-cover-image',
            ])
            : '';

        $isVideo = (bool) ($data['is_video'] ?? false);
        $playIconHtml = $isVideo
            ? PlatformComponentRenderer::render('svg', ['icon' => 'play', 'class' => 'media-play-icon'])
            : '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'cover_image' => $coverImageHtml,
            'play_icon' => $playIconHtml,
        ]);
    }
}
