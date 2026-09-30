<?php

/**
 * Visual media section of a project page (replaces section_media_project).
 * Primary title + free content, then the photo and video parts as their own components:
 *   project_visual_media_gallery_photo  (secondary title + photo galleries)
 *   project_visual_media_gallery_video  (tertiary title + video grid)
 *
 *   {
 *     "component": "project_visual_media_gallery",
 *     "data": {
 *       "title_section_primary": "Visual Media",
 *       "title_section_secondary": "Photo Media Content",
 *       "title_section_tertiary": "Video Media Content",
 *       "content_section_primary": [
 *         { "component": "project_block_visual_and_paragraph", "data": { "text": "...", "tags": [ ... ] } }
 *       ]
 *     }
 *   }
 *
 * data (all optional):
 *   title_section_primary     string  default "Visual Media"          ("" = no title)
 *   title_section_secondary   string  title of the photo part   (default "Photo Media Content", "" = no title)
 *   title_section_tertiary    string  title of the video part   (default "Video Media Content", "" = no title)
 *   content_section_primary   array   components rendered under the primary title. No text is generated here.
 *   photo                     object  extra data for project_visual_media_gallery_photo (e.g. {"grid_max": 3})
 *   video                     object  extra data for project_visual_media_gallery_video (e.g. {"exclude": ["videos_1"]})
 *   show_photo / show_video   bool    default true
 */
class project_visual_media_gallery
{
    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) return '';

        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $primary = section::render_children(array_merge($shared, [
            'children' => project_visual_web_gallery::galleries($data, 'content_section_primary', []),
        ]));
        $photos = self::part($data, 'photo', 'title_section_secondary', project_visual_media_gallery_photo::DEFAULT_TITLE, $shared);
        $videos = self::part($data, 'video', 'title_section_tertiary', project_visual_media_gallery_video::DEFAULT_TITLE, $shared);

        if (trim($primary . $photos . $videos) === '') return '';

        $title = trim((string) ($data['title_section_primary'] ?? 'Visual Media'));

        return ($title !== '' ? project_visual_web_gallery::title($title, $post) : '') . $primary . $photos . $videos;
    }

    private static function part(array $data, string $key, string $titleKey, string $defaultTitle, array $shared): string
    {
        if (!project_media_gallery::flag($data, 'show_' . $key, true)) return '';

        return (string) PlatformComponentRenderer::render('project_visual_media_gallery_' . $key, array_merge(
            project_media_gallery::params($data, $key),
            ['title' => (string) ($data[$titleKey] ?? $defaultTitle)],
            $shared
        ));
    }
}
