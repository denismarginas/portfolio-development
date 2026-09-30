<?php

/**
 *   { "component": "block_video", "data": { "src": "src/content/vid/projects/casadarius-shop/videos_2/casadarius_2.webm" } }
 *
 * Looks the video up in data_items_video.json: an item matches when its data.media.video.video
 * is the end of "src" (e.g. "projects/casadarius-shop/videos_2/casadarius_2.webm"),
 * otherwise when the file names are the same ("casadarius_2.webm").
 *
 * data:
 *   src                  string  video path ("src/content/vid/..." or relative to src/content/vid/)
 *   thumbnail_bg         string  fallback thumbnail when no item is found (url or project path)
 *   video_primary_color  string  color of the controls (default: the item's settings.appearance.colors.primary)
 *   thumbnail_content    string  optional markup inside the thumbnail (passed to the video component)
 *
 * Thumbnail: item media.video.video_thumbnail (relative to src/content/img/) -> thumbnail_bg -> none.
 * Renders the "video" component; nothing when the video file does not exist.
 */
class block_video
{
    private const IMG_DIR = 'src/content/img/';

    /** @var array<int, array>|null */
    private static ?array $items = null;

    public static function render(array $data = []): string
    {
        $src = trim(str_replace('\\', '/', (string) ($data['src'] ?? $data['video'] ?? '')));
        if ($src === '') return '';

        $item = self::find($src);

        $thumbnail = self::item_thumbnail($src);
        if ($thumbnail === '') $thumbnail = trim((string) ($data['thumbnail_bg'] ?? ''));

        $color = trim((string) ($data['video_primary_color'] ?? ''));
        if ($color === '' && $item) $color = trim((string) ($item['settings']['appearance']['colors']['primary'] ?? ''));

        return (string) PlatformComponentRenderer::render('video', array_filter([
            'src' => $src,
            'thumbnail_bg' => $thumbnail,
            'video_primary_color' => $color,
            'thumbnail_content' => (string) ($data['thumbnail_content'] ?? ''),
        ], fn ($v) => $v !== ''));
    }

    /** Thumbnail url of the data_items_video.json item of $src ('' when there is no item / thumbnail file). */
    public static function item_thumbnail(string $src): string
    {
        $item = self::find($src);
        return $item ? self::thumbnail((string) ($item['data']['media']['video']['video_thumbnail'] ?? '')) : '';
    }

    /** Project-relative path of the item thumbnail of $src ("src/content/img/..."); '' when there is no item / file / it is external. */
    public static function item_thumbnail_path(string $src): string
    {
        $item = self::find($src);
        $path = $item ? self::thumbnail_path((string) ($item['data']['media']['video']['video_thumbnail'] ?? '')) : '';
        return PlatformUrlService::is_external_url($path) ? '' : $path;
    }

    /** Video item whose media.video.video matches $src (path suffix first, then file name). */
    public static function find(string $src): ?array
    {
        $src = strtolower(ltrim($src, '/'));
        $name = basename($src);
        $byName = null;

        foreach (self::items() as $item) {
            $video = strtolower(ltrim(str_replace('\\', '/', (string) ($item['data']['media']['video']['video'] ?? '')), '/'));
            if ($video === '') continue;
            if ($src === $video || str_ends_with($src, '/' . $video)) return $item;
            if ($byName === null && basename($video) === $name) $byName = $item;
        }

        return $byName;
    }

    private static function items(): array
    {
        if (self::$items === null) {
            $items = PlatformDataService::get_all_items_from_file('video') ?? [];
            self::$items = array_values(array_filter($items, fn ($i) => is_array($i) && ($i['settings']['render'] ?? true) !== false));
        }
        return self::$items;
    }

    /** Item thumbnail -> asset prefixed url (so it also works in dist pages); '' when the file is missing. */
    private static function thumbnail(string $path): string
    {
        $path = self::thumbnail_path($path);
        if ($path === '' || PlatformUrlService::is_external_url($path)) return $path;

        return rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . $path;
    }

    /** Item thumbnail -> project-relative path (external urls unchanged); '' when the file is missing. */
    private static function thumbnail_path(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '') return '';
        if (PlatformUrlService::is_external_url($path)) return $path;

        $path = str_starts_with(ltrim($path, '/'), 'src/') ? ltrim($path, '/') : self::IMG_DIR . ltrim($path, '/');
        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';

        return is_file($root . $path) ? $path : '';
    }
}
