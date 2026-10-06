<?php

/**
 *   { "component": "link_list", "data": { "title": "Contact Data", "links": [ ... ] } }
 *
 * data:
 *   title      string  optional heading
 *   title_tag  string  "h3" (default) | "h2" | "h4" | "h5"
 *   title_link object  optional, makes the title a link: { "_id": "contact" } (page) or { "url": "..." }
 *   links      array   link entries (see below), rendered in order
 *   class, id  string  optional
 *
 * Static link:
 *   { "text": "Contact Form", "_id": "contact", "anchor": "form-contact-form" }   page link (+ #anchor)
 *   { "text": "GitHub", "url": "https://github.com/...", "target": "_blank" }     any url / file path
 *
 * Query link - one link per result of the query (utility_filter):
 *   {
 *     "text": "CV English",                     optional, default: the result's data.seo.title
 *     "source": {
 *       "item": "resume"  |  "post": "project"  |  "taxonomy": "category",
 *       "filter_by":  { "settings.lang": "en", "max_items": 1 },
 *       "exclude_by": [ ... ],
 *       "sort":       [ { "by": "date.publish", "order": "desc" } ]
 *     },
 *     "link": "media.pdf",                      optional path in the result to use as href
 *                                               (default: the result's page, from its _id)
 *     "anchor": "...", "target": "_blank", "download": true
 *   }
 *
 * Any link may also have "svg" (icon name from src/content/svg) and "class".
 */
class link_list
{
    private const TITLE_TAGS = ['h2', 'h3', 'h4', 'h5'];

    public static function render(array $data = []): string
    {
        $items = '';
        foreach ((array) ($data['links'] ?? []) as $link) {
            if (!is_array($link)) continue;
            $items .= is_array($link['source'] ?? null) ? self::query_links($link) : self::link($link, null);
        }
        if ($items === '') return '';

        $title = trim((string) ($data['title'] ?? ''));
        $tag = strtolower((string) ($data['title_tag'] ?? 'h3'));
        if (!in_array($tag, self::TITLE_TAGS, true)) $tag = 'h3';

        $class = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'title' => $title !== '' ? '<' . $tag . ' class="link-list-title">' . self::title_html($title, $data['title_link'] ?? null) . '</' . $tag . '>' : '',
            'items' => $items,
        ]);
    }

    private static function title_html(string $title, mixed $titleLink): string
    {
        $href = is_array($titleLink) ? self::href($titleLink, null) : '';
        return $href !== ''
            ? '<a class="link-list-title-link" href="' . self::e($href) . '">' . self::e($title) . '</a>'
            : self::e($title);
    }

    /** One <li> per result of link.source. */
    private static function query_links(array $link): string
    {
        $source = $link['source'];
        $results = PlatformComponentRenderer::value('utility_filter', [
            'items' => self::source_items($source),
            'filter_by' => $source['filter_by'] ?? [],
            'exclude_by' => $source['exclude_by'] ?? [],
            'sort' => $source['sort'] ?? null,
            'max_items' => $source['max_items'] ?? 0,
        ]);
        if (!is_array($results)) return '';

        $html = '';
        foreach ($results as $result) {
            $html .= self::link($link, $result);
        }
        return $html;
    }

    private static function source_items(array $source): array
    {
        if (($name = trim((string) ($source['item'] ?? ''))) !== '') {
            $items = PlatformDataService::get_all_items_from_file($name);
        } elseif (($name = trim((string) ($source['post'] ?? ''))) !== '') {
            $items = PlatformDataService::get_all_posts_from_file($name);
        } elseif (($name = trim((string) ($source['taxonomy'] ?? ''))) !== '') {
            $items = PlatformDataService::get_data('taxonomy_' . $name);
        } else {
            $items = null;
        }
        return is_array($items) ? $items : [];
    }

    /** $result = the matched item / post / term for query links, null for static links. */
    private static function link(array $link, ?array $result): string
    {
        $text = trim((string) ($link['text'] ?? ''));
        if ($text === '' && $result !== null) {
            $text = trim((string) (utility_filter::value_at($result, 'seo.title') ?? ''));
        }

        $href = self::href($link, $result);
        if ($text === '' || $href === '') return '';

        $attrs = ' href="' . self::e($href) . '"';
        $target = trim((string) ($link['target'] ?? ''));
        if ($target !== '') {
            $attrs .= ' target="' . self::e($target) . '"' . ($target === '_blank' ? ' rel="noopener"' : '');
        }
        if (($link['download'] ?? false) === true) $attrs .= ' download';

        $class = trim((string) ($link['class'] ?? ''));
        $icon = trim((string) ($link['svg'] ?? '')) !== ''
            ? (string) PlatformComponentRenderer::render('svg', ['icon' => (string) $link['svg'], 'class' => 'link-list-icon'])
            : '';

        return '<li><a class="link-list-link' . ($class !== '' ? ' ' . self::e($class) : '') . '"' . $attrs . '>'
            . $icon . '<span class="link-list-text">' . self::e($text) . '</span></a></li>';
    }

    private static function href(array $link, ?array $result): string
    {
        $anchor = trim((string) ($link['anchor'] ?? ''), " #");
        $anchor = $anchor !== '' ? '#' . rawurlencode($anchor) : '';

        // query link with "link": path inside the result (e.g. media.pdf)
        $path = trim((string) ($link['link'] ?? ''));
        if ($result !== null && $path !== '') {
            $value = utility_filter::value_at($result, $path);
            return is_string($value) && trim($value) !== '' ? self::asset_url($value) . $anchor : '';
        }

        // query link without "link", or static link with "_id": the page of that _id
        $id = $result !== null ? (string) ($result['_id'] ?? '') : trim((string) ($link['_id'] ?? ''));
        if ($id !== '') return PlatformPathService::post_link($id) . $anchor;

        $url = trim((string) ($link['url'] ?? ''));
        return $url !== '' ? self::asset_url($url) . $anchor : ($anchor !== '' ? $anchor : '');
    }

    /** External urls kept; "src/content/..." -> relative to the current page (works in dev and dist). */
    private static function asset_url(string $path): string
    {
        $path = trim($path);
        if ($path === '' || PlatformUrlService::is_external_url($path) || preg_match('#^(mailto:|tel:|\#)#i', $path)) return $path;
        return rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . ltrim($path, '/');
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
