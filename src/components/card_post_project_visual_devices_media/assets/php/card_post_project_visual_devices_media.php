<?php

require_once __DIR__ . '/../../../card_post_project_visual_media/assets/php/parts/project_media_finder.php';
require_once __DIR__ . '/parts/project_video_finder.php';

/**
 * Media device mockups: first photo on a portrait tablet, first video on a
 * landscape tablet (the old devices_post_item_media, in one component).
 *
 * $data options:
 *   post_current_data  array   project post (required)
 *   show               string  "both" (default) | "photo" | "video"
 *   link               bool    wrap in <a> to the post (default true), false -> <div>
 *   lazy               bool    lazy-load the photo (default true)
 *
 * Photo: project_media_finder::photo().
 * Video screen: the first project video (project_video_finder::videos()) that has a video_thumbnail
 * in data_items_video.json is shown as that image; when none has one, the first video itself.
 * A device renders only when its file exists; nothing found -> ''.
 * When both render, the root gets "tablet-and-landscape-devices".
 */
class card_post_project_visual_devices_media
{
    private const HTML_DIR = __DIR__ . '/../html/';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $show = strtolower(trim((string) ($data['show'] ?? 'both')));
        if (!in_array($show, ['both', 'photo', 'video'], true)) $show = 'both';

        $color = htmlspecialchars((string) ($post['settings']['appearance']['colors']['primary'] ?? ''), ENT_QUOTES, 'UTF-8');
        $screenStyle = $color !== '' ? ' style="background-color: ' . $color . ';"' : '';
        $frames = self::frames();
        $lazy = !array_key_exists('lazy', $data) || (bool) $data['lazy'];

        $photoBlock = $show !== 'video' ? self::photo_block($postData, $frames['tablet'], $screenStyle, $lazy) : '';
        $videoBlock = $show !== 'photo' ? self::video_block($postData, $frames['tablet-landscape'], $screenStyle) : '';
        if ($photoBlock === '' && $videoBlock === '') {
            return '';
        }

        $asLink = !array_key_exists('link', $data) || (bool) $data['link'];
        $link = PlatformPathService::post_link((string) ($post['_id'] ?? '')) . '#visualmedia';

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'tag' => $asLink ? 'a' : 'div',
            'href_attr' => $asLink ? ' href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '"' : '',
            'both_devices_check' => ($photoBlock !== '' && $videoBlock !== '') ? ' tablet-and-landscape-devices' : '',
            'color_style' => $color !== '' ? ' style="--primary-color-post: ' . $color . ';"' : '',
            'photo_block' => $photoBlock,
            'video_block' => $videoBlock,
        ]);
    }

    private static function photo_block(array $postData, string $frame, string $screenStyle, bool $lazy): string
    {
        $src = project_media_finder::photo($postData);
        $photo = $src !== '' ? self::image($src, 'Media preview', 'media-photo-image', $lazy) : '';
        if ($photo === '') {
            return '';
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/tablet_block.html', [
            'screen_style' => $screenStyle,
            'photo' => $photo,
            'frame' => self::image($frame, 'Tablet frame', 'tablet', $lazy),
        ]);
    }

    private static function video_block(array $postData, string $frame, string $screenStyle): string
    {
        $videos = project_video_finder::videos($postData);
        if (empty($videos)) {
            return '';
        }

        // first video with a data_items_video.json thumbnail -> that image, otherwise the first video
        $media = '';
        foreach ($videos as $src) {
            $thumbnail = block_video::item_thumbnail_path($src);
            if ($thumbnail !== '') {
                $media = self::image($thumbnail, 'Video preview', 'media-video-thumbnail', true);
                if ($media !== '') break;
            }
        }
        if ($media === '') {
            $url = rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . ltrim($videos[0], '/');
            // "#t=0.1" makes browsers paint the first frame as the poster
            $media = PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/video.html', [
                'video_src' => htmlspecialchars($url . '#t=0.1', ENT_QUOTES, 'UTF-8'),
            ]);
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/tablet_landscape_block.html', [
            'screen_style' => $screenStyle,
            'media' => $media,
            'play_icon' => (string) PlatformComponentRenderer::render('svg', ['icon' => 'play', 'class' => 'media-play-icon']),
            'frame' => self::image($frame, 'Tablet landscape frame', 'tablet-landscape', false),
        ]);
    }

    /** @return array{tablet:string, tablet-landscape:string} */
    private static function frames(): array
    {
        $config = PlatformDataService::get_data('content_post_projects');
        $devices = is_array($config['img']['devices'] ?? null) ? $config['img']['devices'] : [];

        return [
            'tablet' => (string) ($devices['tablet'] ?? ''),
            'tablet-landscape' => (string) ($devices['tablet-landscape'] ?? ''),
        ];
    }

    private static function image(string $src, string $alt, string $class, bool $lazy): string
    {
        return $src !== ''
            ? (string) PlatformComponentRenderer::render('image', ['src' => $src, 'alt' => $alt, 'class' => $class, 'lazy' => $lazy])
            : '';
    }
}
