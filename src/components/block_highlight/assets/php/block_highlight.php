<?php

/**
 * One highlight of a post (a feature, plugin, SEO work, ad, render ...) in a single card, in 4 parts:
 *   1. title
 *   2. media ┐ side by side through the "columns" component (default: 50-50 from md up, one column below)
 *   3. text  ┘
 *   4. text_full (full width, optional) - the rest of a long text, under media + text
 *   5. tags (full width)
 *
 *   {
 *     "component": "block_highlight",
 *     "data": {
 *       "title": "Auto-filled Request Form",
 *       "image": "highlights/auto-filled-request-form/prestashop-ctf7-autocomplet-data.webp",
 *       "text": "This is the Contact Form 7 module, but modified ...",
 *       "tags_title": "Modules used",
 *       "tags": ["Elementor", "Contact Form 7"],
 *       "columns": { "layout": { "base": "100", "lg": "60-40" }, "media_first": false }
 *     }
 *   }
 *
 * data:
 *   title           string  card title (required)
 *   id              string  anchor id (default: title without spaces/symbols, like block_title, e.g. "seooptimization")
 *   tag             string  title tag: "h3" (default) | "h2" | "h4" | "div"
 *   label           string  optional small label above the title (e.g. "WordPress Plugin")
 *   image           string  image path relative to the post media folder (src/content/img/projects/<media.path>/),
 *                           e.g. "highlights/banners/banner.webp", or a full "src/..." path
 *   slider          string  folder with images, relative to the post media folder (block_slider_images)
 *   video           string | array  video path(s) ("src/content/vid/..." or relative to src/content/vid/) (project_block_video)
 *                           with a slider, the video(s) are added as the last slide(s) (see video_in_slider)
 *   video_in_slider bool    false = show the video under the slider instead of inside it (default true)
 *                           image, slider and video can be combined; they stack in the media column in that order
 *   text            string  text; <br>, <b>, <strong>, <i>, <em>, <u> are kept, any other markup is escaped
 *   text_full       string  more text in its own full-width container under the media + text columns
 *                           (same markup rules), for long texts that would leave the media column empty
 *   tags            array   tool / module names shown as chips (strings, or a comma separated string)
 *   tags_title      string  optional label before the chips (e.g. "Modules used"), none by default
 *   columns         object  params for the "columns" component that puts text and media side by side
 *                           (only used when there are both). Defaults:
 *                             layout          { "base": "100", "md": "50-50" }
 *                             gap             "sm"
 *                             align           "start" (top)
 *                             reverse_stacked false
 *                           plus "media_first": false to put the text column before the media (default true: media first)
 *                           and "class" for an extra class on the columns element.
 *   children        array   components rendered inside the card, under text/media and before the tags
 *                           (e.g. a "post-items" list of related projects). They get post_current_data.
 *   class           string  optional extra class
 *
 * Renders nothing without a title, or when there is neither media, text, children output nor tags.
 *
 * Related projects example (the page's own project is left out by "exclude_current"):
 *   { "component": "block_highlight", "data": { "title": "Related Project", "children": [
 *       { "component": "post-items", "data": {
 *           "class": "cols-100 cols-md-50-50 gap-xs gap-sm-sm", "post_type": "project",
 *           "filter_by": { "_id": ["project-a", "project-b", "project-c"] }, "exclude_current": true,
 *           "template": { "component": "card_post_project" } } }
 *   ] } }
 */
class block_highlight
{
    private const HTML_DIR = __DIR__ . '/../html/';
    private const TAGS = ['h2', 'h3', 'h4', 'div'];
    private const ALLOWED_TAGS = ['br', 'b', 'strong', 'i', 'em', 'u'];
    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];
    private const COLUMNS_DEFAULTS = [
        'layout' => ['base' => '100', 'md' => '50-50'],
        'gap' => 'sm',
        'align' => 'start',
        'reverse_stacked' => false,
    ];

    public static function render(array $data = []): string
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') return '';

        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $mediaItems = self::media($data, $shared);
        $text = self::text((string) ($data['text'] ?? ''));
        $textFull = self::text((string) ($data['text_full'] ?? ''));
        $tags = self::tags($data);
        $children = self::children($data, $shared);
        if (!$mediaItems && $text === '' && $textFull === '' && $tags === '' && $children === '') return '';

        $media = $mediaItems
            ? PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/media.html', ['items' => implode('', $mediaItems)])
            : '';
        $content = $text !== ''
            ? PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/content.html', ['text' => $text])
            : '';
        $contentFull = $textFull !== ''
            ? PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/content_full.html', ['text' => $textFull])
            : '';

        $classes = [];
        if ($media === '') $classes[] = 'block-highlight-no-media';
        if ($content === '') $classes[] = 'block-highlight-no-text';
        $extra = trim((string) ($data['class'] ?? ''));
        if ($extra !== '') $classes[] = $extra;

        $tag = strtolower(trim((string) ($data['tag'] ?? 'h3')));
        if (!in_array($tag, self::TAGS, true)) $tag = 'h3';

        $id = array_key_exists('id', $data) ? trim((string) $data['id']) : preg_replace('/[^a-z0-9]/', '', strtolower($title));
        $label = trim((string) ($data['label'] ?? ''));

        return PlatformTemplateRenderer::render([
            'extra_class' => $classes ? ' ' . self::esc(implode(' ', $classes)) : '',
            'id_attr' => $id !== '' ? ' id="' . self::esc($id) . '"' : '',
            'tag' => $tag,
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8', false),
            'label' => $label !== '' ? '<span class="block-highlight-label">' . self::esc($label) . '</span>' : '',
            'body' => self::body($content, $media, $data) . $contentFull . $children,
            'tags' => $tags,
        ]);
    }

    /** Text + media: side by side through "columns" when there are both, otherwise the one that exists. */
    private static function body(string $content, string $media, array $data): string
    {
        if ($content === '' || $media === '') return $content . $media;

        $params = is_array($data['columns'] ?? null) ? $data['columns'] : [];
        $mediaFirst = ($params['media_first'] ?? true) !== false;
        unset($params['media_first'], $params['children']);

        $columns = array_merge(self::COLUMNS_DEFAULTS, $params);
        $columns['class'] = trim('block-highlight-body ' . (is_scalar($params['class'] ?? null) ? (string) $params['class'] : ''));

        $text = ['html' => $content, 'class' => 'block-highlight-column-text'];
        $visual = ['html' => $media, 'class' => 'block-highlight-column-media'];
        $columns['children'] = $mediaFirst ? [$visual, $text] : [$text, $visual];

        return (string) PlatformComponentRenderer::render('columns', $columns);
    }

    /** data.children: components rendered (with post_current_data) inside .block-highlight-children; '' when they render nothing. */
    private static function children(array $data, array $shared): string
    {
        $children = $data['children'] ?? [];
        if (!is_array($children) || $children === []) return '';

        $html = (string) section::render_children(array_merge($shared, ['children' => $children]));
        return trim($html) === '' ? '' : '<div class="block-highlight-children">' . $html . '</div>';
    }

    /** @return string[] rendered media (image, slider, video - in that order), empty ones skipped */
    private static function media(array $data, array $shared): array
    {
        $items = [];

        $image = trim(str_replace('\\', '/', (string) ($data['image'] ?? '')), '/');
        if ($image !== '') {
            $dir = str_contains($image, '/') ? substr($image, 0, strrpos($image, '/')) . '/' : '';
            $items[] = (string) PlatformComponentRenderer::render('block_image', array_merge($shared, [
                'image_path' => $dir,
                'image_file_name' => basename($image),
                'alt' => trim((string) ($data['title'] ?? '')),
                'card' => false,
                'class' => 'block-highlight-image',
            ]));
        }

        $slider = trim(str_replace('\\', '/', (string) ($data['slider'] ?? '')));
        $videos = self::videos($data['video'] ?? []);
        $videoInSlider = $slider !== '' && $videos && ($data['video_in_slider'] ?? true) !== false;

        if ($slider !== '') {
            $items[] = (string) PlatformComponentRenderer::render('block_slider_images', array_merge($shared, [
                'img_array_path_dir' => $slider,
                'videos' => $videoInSlider ? $videos : [],
                'class' => 'block-highlight-slider',
            ]));
        }

        if (!$videoInSlider) {
            foreach ($videos as $video) {
                $items[] = (string) PlatformComponentRenderer::render('project_block_video', array_merge($shared, [
                    'src' => $video,
                    'class' => 'block-highlight-video',
                ]));
            }
        }

        return array_values(array_filter($items, fn ($html) => trim($html) !== ''));
    }

    /** data.video: one path or a list of paths -> list of non-empty paths. */
    private static function videos(mixed $value): array
    {
        $list = is_array($value) ? $value : [$value];
        return array_values(array_filter(array_map(
            fn ($v) => is_scalar($v) ? trim(str_replace('\\', '/', (string) $v)) : '',
            $list
        ), fn ($v) => $v !== ''));
    }

    private static function tags(array $data): string
    {
        $list = $data['tags'] ?? [];
        if (is_string($list)) {
            $decoded = json_decode($list, true);
            $list = is_array($decoded) ? $decoded : explode(',', $list);
        }
        if (!is_array($list)) return '';

        $html = '';
        foreach ($list as $tag) {
            $text = trim(is_array($tag) ? (string) ($tag['text'] ?? '') : (string) $tag);
            if ($text === '') continue;
            $html .= PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/tag.html', ['text' => self::esc($text)]);
        }
        if ($html === '') return '';

        $title = trim((string) ($data['tags_title'] ?? ''));

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/tags.html', [
            'tags_title' => $title !== '' ? '<span class="block-highlight-tags-title">' . self::esc($title) . '</span>' : '',
            'tags' => $html,
        ]);
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

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
