<?php

/**
 * Secondary navbar under the site header (sticky). Below 1024px the links fold into a dropdown opened by a
 * toggle button (the header's menu icon + the name of the section in view).
 *
 *   {
 *     "component": "section_nav",
 *     "data": {
 *       "items": [ { "id": "overview", "text": "Overview" }, { "id": "screens", "text": "Screens" } ]
 *     }
 *   }
 *
 * data:
 *   items        array   { "id" (id of a section on the page), "text" } - links to ids that are not on the page
 *                        are hidden by anchor_index.js; the nav hides itself when none is left
 *   logo         string | true  logo image at the start ("src/..." or relative to src/content/img/projects/);
 *                        true = the post logo (data.media.logo.img, else .svg); default: no logo
 *   logo_link    string  where the logo points (default "#top")
 *   progress     bool    scroll_progress line on the bottom edge (default true)
 *   label        string  aria-label of the links (default "Page sections")
 *   class, id    string  optional
 */
class section_nav
{
    private const PROJECTS_IMG_DIR = 'src/content/img/projects/';

    private static int $instance = 0;

    public static function render(array $data = []): string
    {
        $items = [];
        foreach ((array) ($data['items'] ?? []) as $item) {
            if (!is_array($item)) continue;
            $id = trim(self::str($item['id'] ?? ''), '# ');
            $text = self::str($item['text'] ?? '');
            if ($id !== '' && $text !== '') $items[] = ['id' => $id, 'text' => $text];
        }
        if ($items === []) return '';

        // the links + "section in view" logic are anchor_index (horizontal look in section_nav.scss)
        $index = (string) PlatformComponentRenderer::render('anchor_index', [
            'title' => '',
            'items' => $items,
            'min_items' => 1,
            'class' => 'section-nav-index',
        ]);
        if (trim($index) === '') return '';
        $label = self::str($data['label'] ?? '') ?: 'Page sections';
        $linksId = 'section-nav-links-' . (++self::$instance);
        $index = preg_replace('/aria-label="[^"]*"/', 'id="' . $linksId . '" aria-label="' . self::e($label) . '"', $index, 1);

        $progress = ($data['progress'] ?? true) !== false
            ? (string) PlatformComponentRenderer::render('scroll_progress', ['position' => 'bottom', 'class' => 'section-nav-progress'])
            : '';

        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render([
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'brand' => self::brand($data),
            'label' => self::e($label),
            'links_id' => $linksId,
            'toggle_icon' => (string) PlatformComponentRenderer::render('svg', ['icon' => 'menu', 'class' => 'section-nav-toggle-icon']),
            'current' => self::e($items[0]['text']),
            'index' => $index,
            'progress' => $progress,
        ]);
    }

    /** Logo link: data.logo (a path), "logo": true = the post logo (img -> svg); '' by default. */
    private static function brand(array $data): string
    {
        $option = $data['logo'] ?? false;
        if ($option === false || $option === null || $option === '') return '';

        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        $title = self::str($post['data']['seo']['title'] ?? '');
        $logo = is_array($post['data']['media']['logo'] ?? null) ? $post['data']['media']['logo'] : [];

        $html = '';
        $src = is_string($option) ? self::str($option) : self::str($logo['img'] ?? '');
        if ($src !== '') {
            $html = (string) PlatformComponentRenderer::render('image', [
                'src' => str_starts_with($src, 'src/') ? $src : self::PROJECTS_IMG_DIR . ltrim($src, '/'),
                'alt' => $title !== '' ? 'Logo - ' . $title : 'Logo',
                'class' => 'section-nav-brand-img',
            ]);
        }
        if (trim($html) === '' && ($svg = self::str($logo['svg'] ?? '')) !== '') {
            $html = (string) PlatformComponentRenderer::render('svg', ['icon' => $svg, 'class' => 'section-nav-brand-img']);
        }
        if (trim($html) === '') return '';

        $link = self::str($data['logo_link'] ?? '') ?: '#top';
        return '<a class="section-nav-brand" href="' . self::e($link) . '" aria-label="' . self::e($title !== '' ? $title . ' - top' : 'Top of the page') . '">' . $html . '</a>';
    }

    private static function str(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
