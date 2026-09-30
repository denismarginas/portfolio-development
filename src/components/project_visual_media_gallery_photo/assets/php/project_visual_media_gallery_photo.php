<?php

/**
 * Photo part of a project's visual media (old gallery_media).
 *
 *   { "component": "project_visual_media_gallery_photo", "data": { "title": "Photo Media Content" } }
 *
 * data (all optional):
 *   title      string  default "Photo Media Content" ("" = no title)
 *   galleries  array   project_media_gallery params
 *                      (default: "images" from media/, one list per folder, id "photo" - keeps the old #photo anchor)
 *   grid_max   int     default max columns (default 4)
 *
 * Title + galleries render only when there are images.
 */
class project_visual_media_gallery_photo
{
    public const DEFAULT_TITLE = 'Photo Media Content';

    private const DEFAULT_GALLERIES = [
        [
            'source_gallery_type' => 'images',
            'source_gallery_dir_devices' => 'media',
            'auto_search_galleries_dir' => true,
            'multiple_galleries' => true,
            'id' => 'photo',
        ],
    ];

    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) return '';

        return project_visual_web_gallery::part(
            (string) ($data['title'] ?? self::DEFAULT_TITLE),
            project_visual_web_gallery::galleries($data, 'galleries', self::DEFAULT_GALLERIES),
            $post,
            array_intersect_key($data, array_flip(self::PASSTHROUGH)),
            (int) ($data['grid_max'] ?? 4)
        );
    }
}
