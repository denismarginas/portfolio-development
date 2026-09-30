<?php

/**
 * Web section of a project page (replaces section_web_project).
 *
 *   {
 *     "component": "project_visual_web_gallery",
 *     "data": {
 *       "title_section_primary": "Web Development",
 *       "title_section_secondary": "Web Media Content"
 *     }
 *   }
 *
 * data (all optional):
 *   title_section_primary     string  default "Web Development"   ("" = no title)
 *   title_section_secondary   string  default "Web Media Content" ("" = no title)
 *   galleries_primary         array   project_media_gallery params, rendered under the primary title
 *                                     (default: web/desktop + web/phone, then web/content-website sub galleries)
 *   galleries_secondary       array   project_media_gallery params, rendered under the secondary title
 *                                     (default: web/media-website images, one list per folder)
 *   grid_max                  int     default max columns for every gallery (default 4)
 *
 * A title is rendered only when at least one of its galleries has images.
 * Title ids ("webdevelopment", "webmediacontent") keep the old #anchors working.
 */
class project_visual_web_gallery
{
    private const DEFAULT_PRIMARY = [
        [
            'source_gallery_type' => 'devices',
            'source_gallery_dir_devices' => 'web',
            'source_gallery_dir_web' => 'desktop',
            'source_gallery_dir_phone' => 'phone',
            'id' => 'web',
            'device_params' => [
                'desktop_layout_style' => true,
                'phone_layout_style' => true,
            ],
        ],
        [
            'source_gallery_type' => 'devices',
            'source_gallery_dir_devices' => 'web/content-website',
            'auto_search_galleries_dir' => true,
            'multiple_galleries' => true,
            'id' => 'web-content',
            'device_params' => [
                'desktop_layout_style' => true,
                'phone_layout_style' => true,
            ],
        ],
    ];

    private const DEFAULT_SECONDARY = [
        [
            'source_gallery_type' => 'images',
            'source_gallery_dir_devices' => 'web/media-website',
            'auto_search_galleries_dir' => true,
            'multiple_galleries' => true,
            'id' => 'media-web',
        ],
    ];

    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) return '';

        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));
        $gridMax = (int) ($data['grid_max'] ?? 4);

        $html = self::part(
            (string) ($data['title_section_primary'] ?? 'Web Development'),
            self::galleries($data, 'galleries_primary', self::DEFAULT_PRIMARY),
            $post, $shared, $gridMax
        );
        $html .= self::part(
            (string) ($data['title_section_secondary'] ?? 'Web Media Content'),
            self::galleries($data, 'galleries_secondary', self::DEFAULT_SECONDARY),
            $post, $shared, $gridMax
        );

        return $html;
    }

    /** Title (block_title) + the galleries; '' when no gallery has items. Also used by project_visual_media_gallery. */
    public static function part(string $title, array $galleries, array $post, array $shared, int $gridMax): string
    {
        $html = '';
        foreach ($galleries as $gallery) {
            $html .= (string) PlatformComponentRenderer::render('project_media_gallery', array_merge(['grid_max' => $gridMax], $gallery, $shared));
        }
        if (trim($html) === '') return '';

        $title = trim($title);
        return ($title !== '' ? self::title($title, $post) : '') . $html;
    }

    public static function title(string $text, array $post): string
    {
        return (string) PlatformComponentRenderer::render('block_title', [
            'text' => $text,
            'post_current_data' => $post,
        ]);
    }

    /** List of gallery param sets; accepts an array or JSON text (post editor). */
    public static function galleries(array $data, string $key, array $default): array
    {
        if (!array_key_exists($key, $data)) return $default;
        $value = project_media_gallery::params($data, $key);
        return array_values(array_filter($value, 'is_array'));
    }
}
