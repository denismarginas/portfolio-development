<?php

/**
 * Website screenshots of a project:
 *   src/content/img/projects/<media.path>/web/website/desktop/   web_desktop_0.webp, web_1.webp, web_2.webp ...
 *   src/content/img/projects/<media.path>/web/website/phone/     web_phone_0.webp, web_m_1.webp ...
 *
 * Order: the "_0" image first (file name ending in _0 / -0: web_0, web_desktop_0, web_phone_0, web_m_0),
 * then natural order (web_1, web_2 ... web_10). The "_0" image is the cover (first viewport of the homepage).
 *
 *   utility_project_web_screens::first('agromir-online-shop', 'desktop')
 *       -> "src/content/img/projects/agromir-online-shop/web/website/desktop/web_desktop_0.webp" | ''
 *   utility_project_web_screens::all($mediaPath, 'phone')   -> every phone screenshot, in order
 *   utility_project_web_screens::sort($names)               -> file names / paths, "_0" first then natural
 *   value({ post_current_data, device: "desktop"|"phone" }) -> first()
 */
class utility_project_web_screens
{
    public const PROJECTS_DIR = 'src/content/img/projects/';
    public const WEBSITE_DIR = 'web/website';
    public const DEVICES = ['desktop', 'phone'];
    private const EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png', 'gif', 'avif'];

    public static function value(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        return self::first((string) ($post['data']['media']['path'] ?? ''), (string) ($data['device'] ?? 'desktop'));
    }

    /** Cover screenshot of a device; project-relative path or ''. */
    public static function first(string $mediaPath, string $device = 'desktop', string $baseDir = self::PROJECTS_DIR): string
    {
        return self::all($mediaPath, $device, $baseDir)[0] ?? '';
    }

    /** @return string[] project-relative paths, "_0" first then natural order */
    public static function all(string $mediaPath, string $device = 'desktop', string $baseDir = self::PROJECTS_DIR): array
    {
        $mediaPath = trim(str_replace('\\', '/', $mediaPath), '/');
        if ($mediaPath === '' || str_contains($mediaPath, '..')) return [];

        $device = $device === 'phone' ? 'phone' : 'desktop';
        $base = rtrim($baseDir, '/') . '/' . $mediaPath . '/';

        return self::images($base . self::WEBSITE_DIR . '/' . $device . '/');
    }

    /** @return string[] project-relative image paths in $dir, sorted ("_0" first) */
    public static function images(string $dir): array
    {
        $dir = rtrim($dir, '/') . '/';
        $abs = self::absolute($dir);
        if (!is_dir($abs)) return [];

        $names = [];
        foreach (scandir($abs) ?: [] as $name) {
            if ($name === '' || $name[0] === '.') continue;
            if (!in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) continue;
            if (!is_file($abs . '/' . $name)) continue;
            $names[] = $name;
        }

        return array_map(fn ($name) => $dir . $name, self::sort($names));
    }

    /** File names or paths: "_0" image(s) first, then natural, case-insensitive order of the file names. */
    public static function sort(array $names): array
    {
        $names = array_values($names);
        usort($names, static function ($a, $b): int {
            $firstA = self::is_first((string) $a);
            $firstB = self::is_first((string) $b);
            if ($firstA !== $firstB) return $firstA ? -1 : 1;
            return strnatcasecmp(basename((string) $a), basename((string) $b));
        });
        return $names;
    }

    /** "web_0.webp", "web_desktop_0.webp", "web-m-0.png" -> true; "web_10.webp", "web-0-1.webp" -> false */
    public static function is_first(string $name): bool
    {
        return (bool) preg_match('/(^|[_\-])0+$/', pathinfo(basename($name), PATHINFO_FILENAME));
    }

    private static function absolute(string $path): string
    {
        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';
        return rtrim($root . ltrim($path, '/'), '/');
    }
}
