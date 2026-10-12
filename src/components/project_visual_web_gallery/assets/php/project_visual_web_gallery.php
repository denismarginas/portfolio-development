<?php

require_once __DIR__ . '/../../../utility_project_web_screens/assets/php/utility_project_web_screens.php';

/**
 * Web section of a project page (replaces section_web_project).
 *
 *   {
 *     "component": "project_visual_web_gallery",
 *     "data": {
 *       "title_section_primary": "Web Development",
 *       "title_section_secondary": "Web Media Content",
 *       "content_section_primary": [
 *         { "component": "project_block_visual_and_paragraph", "data": { "text": "...", "tags": [ ... ] } }
 *       ]
 *     }
 *   }
 *
 * data (all optional):
 *   title_section_primary     string  default "Web Development"   ("" = no title)
 *   title_section_secondary   string  default "Web Media Content" ("" = no title)
 *   content_section_primary   array   components rendered under the primary title, before its galleries
 *   galleries_primary         array   project_media_gallery params, rendered under the primary title
 *                                     (default: web/website/desktop + web/website/phone - each "_0" image first -,
 *                                     then web/content-website sub galleries)
 *   galleries_secondary       array   project_media_gallery params, rendered under the secondary title
 *                                     (default: web/media-website images, one list per folder)
 *   grid_max                  int     default max columns for every gallery (default 4)
 *
 * The secondary (Web Media Content) galleries get "column_balancing": true (balanced rows) unless they set it.
 *
 * A title is rendered only when something below it has output (content or gallery images).
 * Components in content_section_primary get "thumbnail_section": "web" (unless they set it), so e.g.
 * project_block_visual_and_paragraph shows data.media.thumbnail.web by default.
 * Title ids ("webdevelopment", "webmediacontent") keep the old #anchors working.
 */
class project_visual_web_gallery
{
    private const DEFAULT_PRIMARY = [
        [
            'source_gallery_type' => 'devices',
            'source_gallery_dir_devices' => utility_project_web_screens::WEBSITE_DIR,
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

        $content = section::render_children(array_merge($shared, [
            'children' => self::with_thumbnail_section(self::galleries($data, 'content_section_primary', []), 'web'),
        ]));

        $html = self::part(
            (string) ($data['title_section_primary'] ?? 'Web Development'),
            self::galleries($data, 'galleries_primary', self::DEFAULT_PRIMARY),
            $post, $shared, $gridMax, (string) $content
        );
        $html .= self::part(
            (string) ($data['title_section_secondary'] ?? 'Web Media Content'),
            self::with_default(self::galleries($data, 'galleries_secondary', self::DEFAULT_SECONDARY), 'column_balancing', true),
            $post, $shared, $gridMax
        );

        return $html;
    }

    /** Title (block_title) + optional content + the galleries; '' when nothing has output. Also used by project_visual_media_gallery. */
    public static function part(string $title, array $galleries, array $post, array $shared, int $gridMax, string $content = ''): string
    {
        $html = '';
        foreach ($galleries as $gallery) {
            $html .= (string) PlatformComponentRenderer::render('project_media_gallery', array_merge(['grid_max' => $gridMax], $gallery, $shared));
        }
        $html = $content . $html;
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

    /** Sets $key on every gallery param set that does not set it itself (e.g. column_balancing). Also used by project_visual_media_gallery_photo. */
    public static function with_default(array $galleries, string $key, mixed $value): array
    {
        foreach ($galleries as $i => $gallery) {
            if (is_array($gallery) && !array_key_exists($key, $gallery)) $galleries[$i][$key] = $value;
        }
        return $galleries;
    }

    /** Adds data.thumbnail_section (utility_post_thumbnail key) to every child that does not set it. Also used by project_visual_media_gallery. */
    public static function with_thumbnail_section(array $children, string $section): array
    {
        foreach ($children as $i => $child) {
            if (!is_array($child)) continue;
            $childData = is_array($child['data'] ?? null) ? $child['data'] : [];
            if (!array_key_exists('thumbnail_section', $childData)) $childData['thumbnail_section'] = $section;
            $children[$i]['data'] = $childData;
        }
        return $children;
    }

    /** List of gallery param sets; accepts an array or JSON text (post editor). */
    public static function galleries(array $data, string $key, array $default): array
    {
        if (!array_key_exists($key, $data)) return $default;
        $value = project_media_gallery::params($data, $key);
        return array_values(array_filter($value, 'is_array'));
    }
}
