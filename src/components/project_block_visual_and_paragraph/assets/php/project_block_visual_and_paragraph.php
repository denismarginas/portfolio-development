<?php

require_once __DIR__ . '/../../../utility_post_thumbnail/assets/php/utility_post_thumbnail.php';

/**
 * Post visual + paragraph with tags, in a single card. Two modes:
 *   "wrap" (default)  the visual is a plain image (200px wide, max 100%, height auto) floated left (or right),
 *                     the text and tags flow next to it and continue full width under it; stacked on xs screens (320px and lower).
 *   "columns"         visual ┐ side by side through the "columns" component (default 40-60 from sm, 30-70 at md,
 *                     text   ┘ 40-60 from lg up, one column below sm); the image has a fixed height, cropped (cover),
 *                     with overlay_noise on top.
 * Optional text_extra: full width under everything.
 *
 *   {
 *     "component": "project_block_visual_and_paragraph",
 *     "data": {
 *       "image": "thumbnail/thumbnail_agromir_web.webp",
 *       "text": "Online shop made in Prestashop 1.7 ...",
 *       "tags": [
 *         { "text": "web", "link": "#web" },
 *         { "text": "media-web", "link": "#media-web" }
 *       ]
 *     }
 *   }
 *
 * data:
 *   mode             string    "wrap" (default) | "columns"
 *   image            string    visual image, one of:
 *                                - a path relative to the post media folder (src/content/img/projects/<media.path>/)
 *                                  or a full "src/..." path, e.g. "thumbnail/thumbnail_agromir_web.webp"
 *                                - a thumbnail reference: "@thumbnail.default" | "@thumbnail.web" | "@thumbnail.media"
 *                                - empty: data.media.thumbnail.<thumbnail_section>, else .default (utility_post_thumbnail)
 *   thumbnail_section string   thumbnail key used when "image" is empty (set automatically to "web" / "media"
 *                              inside project_visual_web_gallery / project_visual_media_gallery; default "default")
 *                              No image found: the visual is post_short_info_visual (post logo on the canvas color).
 *   image_height     string    "columns" mode: height of the image while side by side (default "200px"; px, rem, em or vh)
 *   image_height_stacked string "columns" mode: height of the image while stacked, below sm (default "150px")
 *   text             string    paragraph next to the visual; <br>, <b>, <strong>, <i>, <em>, <u> are kept, any other markup is escaped
 *   text_extra       string    optional paragraph under the visual and the text, full width (same markup rules)
 *   tags             array     block_tags "tags" ({ "text", "link"? } objects or plain strings)
 *   tags_prefix      string    block_tags "prefix": "chevron" (default here) | "hash" | "none"
 *   show_visual      bool      default true
 *   visual           object    extra data for post_short_info_visual, when there is no image (e.g. {"class": "..."})
 *   visual_position  string    "left" (default) | "right" (in "columns" mode same as columns.media_first false)
 *   columns          object    "columns" mode: params for the "columns" component (only used with both a visual and text). Defaults:
 *                                layout  { "base": "100", "sm": "40-60", "md": "30-70", "lg": "40-60" } (mirrored when the visual is on the right)
 *                                gap     "sm"
 *                                align   "start"
 *                              plus "media_first": false to put the text before the visual, "class" for an extra class.
 *   post_id          string    optional, show another post's visual (default: current post)
 *   class, id        string    optional
 *
 * Renders nothing when there is no visual, no text and no tags.
 */
class project_block_visual_and_paragraph
{
    private const HTML_DIR = __DIR__ . '/../html/';
    private const PROJECTS_DIR = 'src/content/img/projects/';
    private const ALLOWED_TAGS = ['br', 'b', 'strong', 'i', 'em', 'u'];
    private const PASSTHROUGH = ['post_current_data', 'post_id', 'global_content_path', 'global_img_path', 'global_vid_path'];
    private const COLUMNS_DEFAULTS = [
        'layout' => ['base' => '100', 'sm' => '40-60', 'md' => '30-70', 'lg' => '40-60'],
        'gap' => 'sm',
        'align' => 'start',
    ];

    public static function render(array $data = []): string
    {
        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $wrap = strtolower(trim((string) ($data['mode'] ?? 'wrap'))) !== 'columns';
        $right = strtolower(trim((string) ($data['visual_position'] ?? 'left'))) === 'right';

        $visual = post_short_info_post::flag($data, 'show_visual', true) ? self::visual($data, $shared, !$wrap) : '';
        // wrap: the floated visual goes inside the content, before the text
        $content = self::content($data, $shared, $wrap ? $visual : '');
        $extraText = self::text((string) ($data['text_extra'] ?? ''));
        if (trim($visual . $content) === '' && $extraText === '') return '';

        $classes = [$wrap ? 'project-block-visual-and-paragraph-mode-wrap' : 'project-block-visual-and-paragraph-mode-columns'];
        if ($right) $classes[] = 'project-block-visual-and-paragraph-visual-right';
        if (trim($visual) === '') $classes[] = 'project-block-visual-and-paragraph-no-visual';
        if (trim($content) === '') $classes[] = 'project-block-visual-and-paragraph-no-content';
        $extra = trim((string) ($data['class'] ?? ''));
        if ($extra !== '') $classes[] = $extra;
        $id = trim((string) ($data['id'] ?? ''));
        $style = self::sizes($data);

        return PlatformTemplateRenderer::render([
            'extra_class' => $classes ? ' ' . self::esc(implode(' ', $classes)) : '',
            'id_attr' => $id !== '' ? ' id="' . self::esc($id) . '"' : '',
            'style_attr' => $style !== '' ? ' style="' . self::esc($style) . '"' : '',
            'body' => $wrap ? $content : self::body($visual, $content, $data),
            'extra' => $extraText !== ''
                ? '<p class="project-block-visual-and-paragraph-text project-block-visual-and-paragraph-text-extra">' . $extraText . '</p>'
                : '',
        ]);
    }

    /** image_height / image_height_stacked -> CSS vars (only valid sizes). */
    private static function sizes(array $data): string
    {
        $vars = [];
        foreach (['image_height' => '--pvp-image-height', 'image_height_stacked' => '--pvp-image-height-stacked'] as $key => $var) {
            $v = is_scalar($data[$key] ?? null) ? strtolower(trim((string) $data[$key])) : '';
            if ($v !== '' && preg_match('/^\d+(\.\d+)?(px|rem|em|vh)$/', $v)) $vars[] = $var . ': ' . $v;
        }
        return implode('; ', $vars);
    }

    /** Visual + content side by side through "columns" when there are both, otherwise the one that exists. */
    private static function body(string $visual, string $content, array $data): string
    {
        if (trim($visual) === '' || trim($content) === '') return $visual . $content;

        $params = self::object($data['columns'] ?? []);
        $visualFirst = ($params['media_first'] ?? true) !== false
            && strtolower(trim((string) ($data['visual_position'] ?? 'left'))) !== 'right';
        unset($params['media_first'], $params['children']);

        $defaults = self::COLUMNS_DEFAULTS;
        if (!$visualFirst) $defaults['layout'] = ['base' => '100', 'sm' => '60-40', 'md' => '70-30', 'lg' => '60-40']; // keep the visual the narrow column
        $columns = array_merge($defaults, $params);
        $columns['class'] = trim('project-block-visual-and-paragraph-columns ' . (is_scalar($params['class'] ?? null) ? (string) $params['class'] : ''));

        $v = ['html' => $visual, 'class' => 'project-block-visual-and-paragraph-column-visual'];
        $c = ['html' => $content, 'class' => 'project-block-visual-and-paragraph-column-content'];
        $columns['children'] = $visualFirst ? [$v, $c] : [$c, $v];

        return (string) PlatformComponentRenderer::render('columns', $columns);
    }

    /** data.image (plain, or with overlay_noise in "columns" mode), or the post logo visual (post_short_info_visual). */
    private static function visual(array $data, array $shared, bool $overlay): string
    {
        $image = self::image_source($data);
        if ($image !== '') {
            $img = (string) PlatformComponentRenderer::render('image', [
                'src' => $image,
                'alt' => (string) ($data['post_current_data']['data']['seo']['title'] ?? ''),
                'class' => 'project-block-visual-and-paragraph-img',
                'lazy' => true,
            ]);
            if (trim($img) !== '') {
                return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/image.html', [
                    'image' => $img,
                    'overlay' => $overlay ? (string) PlatformComponentRenderer::render('overlay_noise', []) : '',
                ]);
            }
        }

        return (string) PlatformComponentRenderer::render('post_short_info_visual', array_merge(
            self::object($data['visual'] ?? []),
            $shared,
            ['class' => 'project-block-visual-and-paragraph-visual']
        ));
    }

    private static function content(array $data, array $shared, string $visual = ''): string
    {
        $text = self::text((string) ($data['text'] ?? ''));
        $prefix = trim((string) ($data['tags_prefix'] ?? 'chevron'));
        $tags = (string) PlatformComponentRenderer::render('block_tags', array_merge($shared, [
            'tags' => $data['tags'] ?? [],
            'prefix' => $prefix,
            'class' => 'project-block-visual-and-paragraph-tags',
        ]));
        if ($text === '' && trim($tags) === '' && trim($visual) === '') return '';

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/content.html', [
            'text' => $visual . ($text !== '' ? '<p class="project-block-visual-and-paragraph-text">' . $text . '</p>' : ''),
            'tags' => $tags,
        ]);
    }

    /** data.image (path or "@thumbnail.<key>"), or the post thumbnail of data.thumbnail_section -> project path ('' when none). */
    private static function image_source(array $data): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        $raw = trim((string) ($data['image'] ?? ''));

        if ($raw === '') return utility_post_thumbnail::get($post, (string) ($data['thumbnail_section'] ?? 'default'));
        if (utility_post_thumbnail::is_reference($raw)) return utility_post_thumbnail::reference($raw, $post);
        return self::image_path($raw, $post);
    }

    /** Image path relative to the post media folder (or "src/...") -> project path, '' when the file does not exist. */
    private static function image_path(string $path, mixed $post): string
    {
        $path = trim(str_replace('\\', '/', $path), '/');
        if ($path === '' || str_contains($path, '..')) return '';

        $candidates = [];
        if (str_starts_with($path, 'src/')) {
            $candidates[] = $path;
        } else {
            $media = is_array($post) ? trim(str_replace('\\', '/', (string) ($post['data']['media']['path'] ?? '')), '/') : '';
            if ($media !== '') $candidates[] = self::PROJECTS_DIR . $media . '/' . $path;
            $candidates[] = self::PROJECTS_DIR . $path;
        }

        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';
        foreach ($candidates as $candidate) {
            if (is_file($root . $candidate)) return $candidate;
        }
        return '';
    }

    /** Escapes the text, then restores the allowed tags (without attributes) - same rules as block_paragraph. */
    private static function text(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') return '';

        $html = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8', false);
        $tags = implode('|', self::ALLOWED_TAGS);
        $html = preg_replace_callback(
            '#&lt;\s*(/?)\s*(' . $tags . ')\s*/?\s*&gt;#i',
            fn ($m) => strtolower($m[2]) === 'br' ? '<br>' : '<' . $m[1] . strtolower($m[2]) . '>',
            $html
        );

        return trim(preg_replace('#^(\s*<br>)+|(<br>\s*)+$#', '', $html));
    }

    /** Object option; the editor stores objects as JSON text. */
    private static function object(mixed $value): array
    {
        if (is_string($value)) $value = json_decode($value, true);
        return is_array($value) ? $value : [];
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
