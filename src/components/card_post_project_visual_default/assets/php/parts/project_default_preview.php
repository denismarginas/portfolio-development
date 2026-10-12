<?php

require_once __DIR__ . '/../../../../card_post_project_visual_media/assets/php/parts/project_media_finder.php';
require_once __DIR__ . '/../../../../utility_post_thumbnail/assets/php/utility_post_thumbnail.php';
require_once __DIR__ . '/../../../../utility_project_web_screens/assets/php/utility_project_web_screens.php';

/**
 * Preview (hover) image of a project, first one that exists:
 *   1. data.media.thumbnail.default  (utility_post_thumbnail; old "thumbnail" string / "thumbnail_img" still work)
 *   2. "Web Development Projects" -> desktop cover: <media.path>/web/website/desktop/web_desktop_0.webp
 *      (utility_project_web_screens: the "_0" image, else the first desktop screenshot)
 *   3. first media photo (project_media_finder: overview_media, media/overview/,
 *      first media folder without "logo")
 * Returns a project-relative path or ''.
 */
class project_default_preview
{
    private const PROJECTS_DIR = 'src/content/img/projects/';
    private const WEB_CATEGORY = 'Web Development Projects';

    public static function find(array $postData): string
    {
        $media = is_array($postData['media'] ?? null) ? $postData['media'] : [];

        $path = utility_post_thumbnail::get($postData, 'default');
        if ($path !== '') return $path;

        $mediaPath = trim((string) ($media['path'] ?? ''), '/');
        $categories = (array) ($postData['taxonomy']['category'] ?? []);
        if ($mediaPath !== '' && in_array(self::WEB_CATEGORY, $categories, true)) {
            $path = utility_project_web_screens::first($mediaPath, 'desktop');
            if ($path !== '') return $path;
        }

        return project_media_finder::photo($postData);
    }

}
