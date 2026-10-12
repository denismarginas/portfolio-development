<?php

/**
 * Thumbnails of a post (data.media.thumbnail):
 *
 *   "thumbnail": {
 *     "default": "agromir/thumbnail/thumbnail_agromir.webp",        shared links (og:image), project cards, fallback
 *     "web":     "agromir/thumbnail/thumbnail_agromir_web.webp",    Web Development section
 *     "media":   "agromir/thumbnail/thumbnail_agromir_media.webp"   Visual Media section / visual-media card
 *   }
 *
 * Paths are relative to src/content/img/projects/ (or a full "src/..." path).
 * Old forms still work as "default": "thumbnail": "<path>" and "thumbnail_img": "<path>".
 *
 * When a key is not set, the generated file is used if it exists (platform: Generate > Generate Thumbnails):
 *   <media.path>/thumbnail/thumbnail_<media.path>.webp         default
 *   <media.path>/thumbnail/thumbnail_<media.path>_web.webp     web
 *   <media.path>/thumbnail/thumbnail_<media.path>_media.webp   media
 *
 *   utility_post_thumbnail::get($postData, 'web')            -> "src/content/img/projects/agromir/thumbnail/..." or ''
 *   utility_post_thumbnail::reference('@thumbnail.web', $postData)
 *
 * Only existing files are returned. Any key other than "default" falls back to "default".
 * $postData is the post's "data" object (or the whole post: its "data" is used).
 */
class utility_post_thumbnail
{
    public const KEYS = ['default', 'web', 'media'];
    private const PROJECTS_DIR = 'src/content/img/projects/';
    private const REFERENCE = '@thumbnail.';

    /** Project-relative path of the thumbnail $key (falls back to "default" when $fallback), '' when none exists. */
    public static function get(array $postData, string $key = 'default', bool $fallback = true): string
    {
        $media = self::media($postData);
        $key = strtolower(trim($key));

        $path = self::existing(self::raw($media, $key));
        if ($path === '') $path = self::existing(self::generated($media, $key));
        if ($path === '' && $fallback && $key !== 'default') {
            $path = self::existing(self::raw($media, 'default'));
            if ($path === '') $path = self::existing(self::generated($media, 'default'));
        }
        return $path;
    }

    /** True when $value is a "@thumbnail.<key>" reference. */
    public static function is_reference(string $value): bool
    {
        return str_starts_with(strtolower(trim($value)), self::REFERENCE);
    }

    /** "@thumbnail.web" -> get($postData, 'web'); '' for anything else. */
    public static function reference(string $value, array $postData): string
    {
        if (!self::is_reference($value)) return '';
        return self::get($postData, substr(trim($value), strlen(self::REFERENCE)));
    }

    /** Raw (unchecked) value of $key, with the old string / thumbnail_img forms as "default". */
    private static function raw(array $media, string $key): string
    {
        $thumb = $media['thumbnail'] ?? null;

        if (is_array($thumb)) {
            $value = $thumb[$key] ?? '';
            if (is_string($value) && trim($value) !== '') return trim($value);
        } elseif ($key === 'default' && is_string($thumb) && trim($thumb) !== '') {
            return trim($thumb);
        }

        if ($key === 'default' && is_string($media['thumbnail_img'] ?? null)) {
            return trim($media['thumbnail_img']);
        }
        return '';
    }

    /** Conventional name of a generated thumbnail, e.g. "zozo/thumbnail/thumbnail_zozo_web.webp". */
    private static function generated(array $media, string $key): string
    {
        $dir = trim(str_replace('\\', '/', (string) ($media['path'] ?? '')), '/');
        if ($dir === '' || !in_array($key, self::KEYS, true)) return '';
        $suffix = $key === 'default' ? '' : '_' . $key;
        return $dir . '/thumbnail/thumbnail_' . str_replace('/', '_', $dir) . $suffix . '.webp';
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
