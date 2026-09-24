<?php

require_once __DIR__ . '/../../../../card_post_project_visual_media/assets/php/parts/project_media_finder.php';

/**
 * Preview (hover) image of a project, first one that exists:
 *   1. data.media.thumbnail / data.media.thumbnail_img  (relative to src/content/img/projects/)
 *   2. "Web Development Projects" -> <media.path>/web/overview/web_desktop_overview.webp
 *   3. first media photo (project_media_finder: overview_media, media/overview/,
 *      first media folder without "logo")
 * Returns a project-relative path or ''.
 */
class project_default_preview
{
    private const PROJECTS_DIR = 'src/content/img/projects/';
    private const WEB_CATEGORY = 'Web Development Projects';
    private const WEB_OVERVIEW = 'web/overview/web_desktop_overview.webp';

    public static function find(array $postData): string
    {
        $media = is_array($postData['media'] ?? null) ? $postData['media'] : [];

        foreach (['thumbnail', 'thumbnail_img'] as $key) {
            $path = self::existing((string) ($media[$key] ?? ''));
            if ($path !== '') return $path;
        }

        $mediaPath = trim((string) ($media['path'] ?? ''), '/');
        $categories = (array) ($postData['taxonomy']['category'] ?? []);
        if ($mediaPath !== '' && in_array(self::WEB_CATEGORY, $categories, true)) {
            $path = self::existing($mediaPath . '/' . self::WEB_OVERVIEW);
            if ($path !== '') return $path;
        }

        return project_media_finder::photo($postData);
    }

    private static function existing(string $relative): string
    {
        if ($relative === '') return '';
        $path = self::PROJECTS_DIR . ltrim($relative, '/');
        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';
        return is_file($root . $path) ? $path : '';
    }
}
