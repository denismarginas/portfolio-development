<?php

/**
 * Background video for a container. Put it as the first child of a position: relative container
 * (hero_home does it for you); it fills the container (absolute, inset 0, object-fit cover).
 *
 *   {
 *     "component": "video_background",
 *     "data": {
 *       "video_id": "denismarginas-experience-highlights",
 *       "start": 0,
 *       "end": 30,
 *       "loop": true,
 *       "audio": false,
 *       "overlay": 0.55
 *     }
 *   }
 *
 * data:
 *   video_id   string  _id of an item in data_items_video.json (uses its video and thumbnail)
 *   src        string  or a direct video path (relative to src/content/vid/, src/content/ or a full "src/..." path / URL)
 *   poster     string  image shown before the video plays (default: the item's video_thumbnail), relative to src/content/img/
 *   start      number  seconds where playback starts (default 0)
 *   end        number|"auto"  seconds where it stops / loops back (default "auto" = the end of the video)
 *   duration   number  alternative to end: play this many seconds from start (e.g. 30)
 *   loop       bool    loop the segment (default true); false = play once and stop on the last frame
 *   audio      bool    sound on (default false = muted). Browsers block autoplay with sound, so it starts muted
 *                      and a small sound toggle button is shown
 *   overlay    number  0..1 strength of the tint over the video (default 0.55, follows light / dark mode), 0 = none
 *   position   string  object-position of the video (default "center")
 *   class, id  string  optional
 *
 * Same options as HTML attributes on any element (no PHP needed, video_background.js must be loaded):
 *   <section data-video-bg="../src/content/vid/x.webm" data-video-bg-start="0" data-video-bg-end="30" (or data-video-bg-duration="30")
 *            data-video-bg-loop="true" data-video-bg-audio="false" data-video-bg-overlay="0.5" data-video-bg-poster="...">
 */
class video_background
{
    public static function render(array $data = []): string
    {
        $item = self::item((string) ($data['video_id'] ?? ''));
        $video = $item['data']['media']['video'] ?? [];

        $src = trim((string) ($data['src'] ?? $video['video'] ?? ''));
        $src = self::asset($src, ['src/content/vid/', 'src/content/']);
        if ($src === '') return '';

        $poster = trim((string) ($data['poster'] ?? $video['video_thumbnail'] ?? ''));
        $poster = $poster !== '' ? self::asset($poster, ['src/content/img/', 'src/content/']) : '';

        $start = max(0, (float) ($data['start'] ?? 0));
        $endRaw = $data['end'] ?? 'auto';
        if (is_numeric($data['duration'] ?? null) && (float) $data['duration'] > 0) $endRaw = $start + (float) $data['duration'];
        $end = is_numeric($endRaw) && (float) $endRaw > $start ? (float) $endRaw : null;
        $loop = ($data['loop'] ?? true) !== false;
        $audio = ($data['audio'] ?? false) === true;
        $overlay = is_numeric($data['overlay'] ?? null) ? max(0, min(1, (float) $data['overlay'])) : 0.55;
        $position = preg_replace('/[^a-z0-9% .-]/i', '', (string) ($data['position'] ?? 'center')) ?: 'center';

        $attrs = ' data-start="' . self::num($start) . '"'
            . ($end !== null ? ' data-end="' . self::num($end) . '"' : '')
            . ' data-loop="' . ($loop ? 'true' : 'false') . '"'
            . ' data-audio="' . ($audio ? 'true' : 'false') . '"'
            . ' style="--video-bg-overlay: ' . self::num($overlay) . '; --video-bg-position: ' . self::e($position) . ';'
            . ($poster !== '' ? ' --video-bg-poster: url(\'' . self::e($poster) . '\');' : '') . '"';

        $class = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        $soundButton = '';
        if ($audio) {
            $soundButton = '<button class="video-background-sound" type="button" data-video-background-sound aria-pressed="false" aria-label="Turn sound on" title="Sound">'
                . '<svg class="video-background-sound-off" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M17 9l4 6M21 9l-4 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
                . '<svg class="video-background-sound-on" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M16.5 8.5a5 5 0 0 1 0 7M19 6a8.5 8.5 0 0 1 0 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
                . '</button>';
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'attrs' => $attrs,
            'poster_attr' => $poster !== '' ? ' poster="' . self::e($poster) . '"' : '',
            'src' => self::e($src),
            'type' => str_ends_with(strtolower(parse_url($src, PHP_URL_PATH) ?? ''), '.mp4') ? 'video/mp4' : 'video/webm',
            'overlay' => $overlay > 0 ? '<span class="video-background-overlay"></span>' : '',
            'sound_button' => $soundButton,
        ]);
    }

    /** Item from data_items_video.json by _id. */
    private static function item(string $id): array
    {
        $id = trim($id);
        if ($id === '') return [];
        foreach (PlatformDataService::get_all_items_from_file('video') ?? [] as $item) {
            if (is_array($item) && ($item['_id'] ?? '') === $id) return $item;
        }
        return [];
    }

    /** Project path -> URL usable from the current page (preview or dist), like the video component. */
    private static function asset(string $path, array $bases): string
    {
        if ($path === '') return '';
        if (PlatformUrlService::is_external_url($path)) return $path;
        $raw = ltrim($path, '/');
        $candidates = [$raw];
        if (!str_starts_with($raw, 'src/')) {
            foreach ($bases as $base) $candidates[] = $base . $raw;
        }
        foreach ($candidates as $cand) {
            $abs = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT . '/' . $cand : $cand;
            if (file_exists($abs)) {
                return rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . ltrim($cand, '/');
            }
        }
        return '';
    }

    private static function num(float $n): string
    {
        return rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.') ?: '0';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
