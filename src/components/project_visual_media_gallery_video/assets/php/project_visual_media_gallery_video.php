<?php

/**
 * Video part of a project's visual media (old video_media / renderVideoMedia).
 *
 *   { "component": "project_visual_media_gallery_video", "data": { "title": "Video Media Content" } }
 *   { "component": "project_visual_media_gallery_video", "data": { "title": "", "dir": "web" } }
 *
 * data (all optional):
 *   title      string  default "Video Media Content" ("" = no title)
 *   dir        string  folder under src/content/vid/projects/<media.path>/ (default "" = that folder)
 *   exclude    array   sub folders / files to skip, e.g. ["videos_1"], ["videos_1/agromir_7.webm"]
 *   grid_max   int     max columns (default 2)
 *   galleries  array   full project_media_gallery params list, replaces the default built from dir / exclude / grid_max
 *
 * Every video is a block_video (thumbnail / color from data_items_video.json when the file is listed there).
 * Title + videos render only when there are videos.
 */
class project_visual_media_gallery_video
{
    public const DEFAULT_TITLE = 'Video Media Content';

    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) return '';

        $gridMax = (int) ($data['grid_max'] ?? 2);
        $default = [[
            'source_gallery_type' => 'videos',
            'source_gallery_dir_devices' => trim((string) ($data['dir'] ?? ''), '/'),
            'auto_search_galleries_dir' => true,
            'multiple_galleries' => true,
            'exclude' => project_media_gallery::strings($data, 'exclude'),
            'grid_max' => $gridMax,
            'id' => 'video',
        ]];

        return project_visual_web_gallery::part(
            (string) ($data['title'] ?? self::DEFAULT_TITLE),
            project_visual_web_gallery::galleries($data, 'galleries', $default),
            $post,
            array_intersect_key($data, array_flip(self::PASSTHROUGH)),
            $gridMax
        );
    }
}
