<?php

/**
 *   { "component": "block_slider_images", "data": { "img_array_path_dir": "web/seo-optimization/" } }
 *
 * data:
 *   img_array_path_dir  string  folder relative to the post media folder
 *                               (src/content/img/projects/<media.path>/), or a full "src/..." path
 *   show_numbers        bool    "i / N" counter (default true)
 *   show_arrows         bool    prev / next buttons (default true)
 *   show_dots           bool    dots under the slider (default false)
 *                               (arrows / counter / dots: utility_slider, only with more than one image)
 *   alt                 string  alt text of the images (default: post title)
 *   videos              string | array  optional video path(s) added as slides after the images
 *                               ("src/content/vid/..." or relative to src/content/vid/), rendered with
 *                               project_block_video (thumbnail from data_items_video.json). Video slides play in
 *                               place (no popup on click) and are part of the popup gallery of the image slides.
 *   class               string  optional extra class
 *   id                  string  optional id
 *
 * Images (jpg, jpeg, png, webp, gif, avif, svg) are sorted by natural file name order.
 * Renders nothing when there are neither images nor videos.
 */
class block_slider_images
{
    private const PROJECTS_DIR = 'src/content/img/projects/';
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg'];

    private static int $instance = 0;

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];

        $dir = self::directory((string) ($data['img_array_path_dir'] ?? $data['path'] ?? ''), $post);
        $images = $dir !== '' ? self::images($dir) : [];
        $videos = self::videos($data);
        $count = count($images) + count($videos);
        if ($count === 0) return '';

        self::$instance++;
        $group = 'block-slider-images-' . substr(md5(($post['_id'] ?? '') . '|' . $dir . '|' . implode('|', $videos)), 0, 8) . '-' . self::$instance;
        $alt = trim((string) ($data['alt'] ?? ($post['data']['project']['title'] ?? $post['data']['seo']['title'] ?? '')));

        $htmlDir = dirname(__DIR__) . '/html/parts/';
        $slides = '';
        foreach ($images as $i => $src) {
            $slides .= PlatformTemplateRenderer::render($htmlDir . 'slide.html', [
                'index' => $i,
                'group' => $group,
                'image' => (string) PlatformComponentRenderer::render('image', [
                    'src' => $src,
                    'alt' => $alt,
                    'class' => 'block-slider-images-img',
                    'lazy' => $i > 0,
                ]),
            ]);
        }

        $index = count($images);
        $shared = array_intersect_key($data, array_flip(['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path']));
        foreach ($videos as $src) {
            $video = (string) PlatformComponentRenderer::render('project_block_video', array_merge($shared, ['src' => $src]));
            if (trim($video) === '') { $count--; continue; }
            $slides .= PlatformTemplateRenderer::render($htmlDir . 'slide_video.html', [
                'index' => $index++,
                'group' => $group,
                'video' => $video,
            ]);
        }
        if ($count === 0) return '';

        $classes = [];
        if ($count === 1) $classes[] = 'block-slider-images-single';
        $extra = trim((string) ($data['class'] ?? ''));
        if ($extra !== '') $classes[] = $extra;
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render([
            'extra_class' => $classes ? ' ' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') : '',
            'id_attr' => $id !== '' ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : '',
            'slider_attrs' => (string) PlatformComponentRenderer::value('utility_slider', [
                'arrows' => self::flag($data, 'show_arrows', true),
                'counter' => self::flag($data, 'show_numbers', true),
                'dots' => self::flag($data, 'show_dots', false),
            ]),
            'label' => htmlspecialchars($alt !== '' ? $alt . ' - images' : 'Images', ENT_QUOTES, 'UTF-8'),
            'slides' => $slides,
        ]);
    }

    /** Project-relative folder ("src/.../") or "" when it doesn't exist. */
    private static function directory(string $path, array $post): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        if ($path === '' || str_contains($path, '..')) return '';

        if (str_starts_with($path, 'src/')) {
            return is_dir(self::absolute($path)) ? $path . '/' : '';
        }

        $media = trim(str_replace('\\', '/', (string) ($post['data']['media']['path'] ?? '')), '/');
        $candidates = [];
        if ($media !== '') $candidates[] = self::PROJECTS_DIR . $media . '/' . $path;
        $candidates[] = self::PROJECTS_DIR . $path;

        foreach ($candidates as $candidate) {
            if (is_dir(self::absolute($candidate))) return $candidate . '/';
        }
        return '';
    }

    /** @return string[] video paths from data.videos (string or array) */
    private static function videos(array $data): array
    {
        $videos = $data['videos'] ?? [];
        if (is_string($videos)) $videos = [$videos];
        if (!is_array($videos)) return [];
        return array_values(array_filter(array_map(
            fn ($v) => is_scalar($v) ? trim(str_replace('\\', '/', (string) $v)) : '',
            $videos
        ), fn ($v) => $v !== ''));
    }

    /** @return string[] project-relative image paths, natural order */
    private static function images(string $dir): array
    {
        $abs = self::absolute($dir);
        $names = array_values(array_filter(
            scandir($abs) ?: [],
            fn ($name) => $name[0] !== '.'
                && is_file($abs . '/' . $name)
                && in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::EXTENSIONS, true)
        ));
        natcasesort($names);
        return array_map(fn ($name) => $dir . $name, array_values($names));
    }

    private static function absolute(string $path): string
    {
        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';
        return rtrim($root . ltrim($path, '/'), '/');
    }

    private static function flag(array $data, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $data)) return $default;
        $v = $data[$key];
        if (is_bool($v)) return $v;
        return !in_array(strtolower(trim((string) $v)), ['false', '0', 'no', 'off', ''], true);
    }
}
