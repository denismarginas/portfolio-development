<?php

/**
 * The single thumbnail of a post (data.media.thumbnail):
 *
 *   "thumbnail": { "default": "agromir-online-shop/thumbnail/thumbnail.webp" }
 *
 * Used for shared links (og:image), project cards and every project section.
 * The path is relative to src/content/img/projects/ (or a full "src/..." path).
 * Old forms still work: "thumbnail": "<path>" and "thumbnail_img": "<path>".
 *
 * When it is not set, <media.path>/thumbnail/thumbnail.webp is used if it exists
 * (the file made by the platform: Generate > Thumbnails).
 *
 *   utility_post_thumbnail::get($postData)                       -> "src/content/img/projects/<path>/thumbnail/thumbnail.webp" or ''
 *   utility_post_thumbnail::reference('@thumbnail', $postData)   (also "@thumbnail.default")
 *
 * Only existing files are returned. $postData is the post's "data" object (or the whole post: its "data" is used).
 */
class utility_post_thumbnail
{
    private const PROJECTS_DIR = 'src/content/img/projects/';
    private const REFERENCE = '@thumbnail';

    /** Project-relative path of the post thumbnail, '' when none exists. ($key is ignored: there is one thumbnail.) */
    public static function get(array $postData, string $key = 'default'): string
    {
        $media = self::media($postData);
        $path = self::existing(self::raw($media));
        if ($path === '') $path = self::existing(self::generated($media));
        return $path;
    }

    /** True when $value is a "@thumbnail" reference ("@thumbnail.<anything>" is read the same way). */
    public static function is_reference(string $value): bool
    {
        return str_starts_with(strtolower(trim($value)), self::REFERENCE);
    }

    /** "@thumbnail" -> get($postData); '' for anything else. */
    public static function reference(string $value, array $postData): string
    {
        return self::is_reference($value) ? self::get($postData) : '';
    }

    /** Raw (unchecked) thumbnail value: "thumbnail.default", the old "thumbnail" string or "thumbnail_img". */
    private static function raw(array $media): string
    {
        $thumb = $media['thumbnail'] ?? null;
        if (is_array($thumb) && is_string($thumb['default'] ?? null) && trim($thumb['default']) !== '') {
            return trim($thumb['default']);
        }
        if (is_string($thumb) && trim($thumb) !== '') return trim($thumb);
        if (is_string($media['thumbnail_img'] ?? null)) return trim($media['thumbnail_img']);
        return '';
    }

    /** Conventional name of the generated thumbnail: "<media.path>/thumbnail/thumbnail.webp". */
    private static function generated(array $media): string
    {
        $dir = trim(str_replace('\\', '/', (string) ($media['path'] ?? '')), '/');
        return $dir === '' ? '' : $dir . '/thumbnail/thumbnail.webp';
    }

    private static function existing(string $relative): string
    {
        $relative = trim(str_replace('\\', '/', $relative), '/');
        if ($relative === '' || str_contains($relative, '..')) return '';

        $path = str_starts_with($relative, 'src/') ? $relative : self::PROJECTS_DIR . $relative;
        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';
        return is_file($root . $path) ? $path : '';
    }

    private static function media(array $postData): array
    {
        if (isset($postData['data']) && is_array($postData['data'])) $postData = $postData['data'];
        return is_array($postData['media'] ?? null) ? $postData['media'] : [];
    }
}
