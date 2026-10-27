<?php

require_once __DIR__ . '/../../../utility_project_media/assets/php/utility_project_media.php';

/**
 * Brand & media section: the web media images of a project (default web/media-website: banners, logo, wallpapers ...)
 * in the same masonry as the photo gallery (it renders media_gallery_filterable): every image at its own shape,
 * logos first and contained on the canvas color, every image opens in the popup gallery. A <section> of its own.
 *
 *   { "component": "media_bento_grid", "data": { "dir": "web/media-website" } }
 *
 * data (all optional):
 *   dir                          folder under the project media folder (default "web/media-website");
 *                                its images and every sub folder are used (folder name = label)
 *   exclude                      sub folders / files to skip
 *   filters                      bool, filter chips per folder (default false)
 *   page_size                    int, frames shown first (default 0 = all)
 *   kicker, title, text, count   section_head texts ("{total}" = number of images, "{folders}" = the folder names)
 *   id                           default "brand"
 *   variant                      section variant (default none)
 */
class media_bento_grid
{
    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) return '';

        $dir = trim((string) ($data['dir'] ?? 'web/media-website'), '/');
        $exclude = is_array($data['exclude'] ?? null) ? $data['exclude'] : [];
        $galleries = utility_project_media::galleries($post, $dir, $exclude);
        if ($galleries === []) return '';

        $total = array_sum(array_map(fn ($g) => count($g['images']), $galleries));
        $names = array_map(fn ($g) => strtolower(self::singular($g['label'])), $galleries);
        $vars = ['{total}' => (string) $total, '{folders}' => self::list_text($names)];

        return (string) PlatformComponentRenderer::render('media_gallery_filterable', [
            'post_current_data' => $post,
            'dir' => $dir,
            'exclude' => $exclude,
            'filters' => ($data['filters'] ?? false) === true,
            'page_size' => (int) ($data['page_size'] ?? 0),
            'featured' => false,
            'interleave' => false,
            'logo_first' => true,
            'id' => array_key_exists('id', $data) ? trim((string) $data['id']) : 'brand',
            'variant' => $data['variant'] ?? '',
            'section_class' => 'media-bento-section',
            'kicker' => strtr(self::opt($data, 'kicker', utility_project_media::text('brand_kicker', 'Brand & media')), $vars),
            'title' => strtr(self::opt($data, 'title', utility_project_media::text('brand_title', 'Brand <em>& media</em>')), $vars),
            'text' => strtr(self::opt($data, 'text', utility_project_media::text('brand_text', 'Made for the website: {folders}.')), $vars),
            'count' => strtr(self::opt($data, 'count', utility_project_media::text('brand_count', '{total} assets')), $vars),
        ]);
    }

    /** "Banners" -> "Banner", "Wallpapers" -> "Wallpaper" (labels of one tile). */
    private static function singular(string $label): string
    {
        return preg_match('/[^s]s$/i', $label) ? substr($label, 0, -1) : $label;
    }

    /** ["banner", "logo", "wallpaper"] -> "banner, logo and wallpaper" */
    private static function list_text(array $names): string
    {
        $names = array_values(array_unique(array_filter($names)));
        if (count($names) < 2) return implode('', $names);
        $last = array_pop($names);
        return implode(', ', $names) . ' ' . utility_project_media::text('and', 'and') . ' ' . $last;
    }

    private static function opt(array $data, string $key, string $default): string
    {
        return array_key_exists($key, $data) ? trim((string) $data[$key]) : $default;
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
