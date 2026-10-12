<?php

/**
 * One card:
 *   { "component": "card_mini_icon_and_text",
 *     "data": { "svg": "employ", "text": "Employee Experience", "link": { "post_id": "employee-experience" } } }
 *
 * A grid of cards ("items"; the card fields set on data apply to every item as defaults):
 *   { "component": "card_mini_icon_and_text",
 *     "data": {
 *       "variant": "row",
 *       "img": "design-elements/overlay-texture-textile.webp",
 *       "items": [
 *         { "svg": "employ", "text": "Employee Experience", "link": { "post_id": "employee-experience" } },
 *         { "svg": "resume", "text": "Education Experience", "link": { "post_id": "education-experience" } }
 *       ]
 *     } }
 *
 * card fields:
 *   svg      string  icon name from src/content/svg (without .svg)
 *   text     string  label
 *   link     string | { post_id }  optional; the card becomes an <a>
 *   target   string  optional link target (e.g. "_blank")
 *   img      string  optional background image (relative to src/content/img/ or a "src/..." path)
 *   variant  string  "row" (default): icon block left, text right | "stack": icon above text, centered, taller
 *   class    string  optional extra class
 *
 * grid options (with "items"):
 *   columns  int     max columns on big screens (balanced grid via utility_grid_listing);
 *                    omitted -> auto-fit columns of min "min_width"
 *   min_width string CSS width of one column in auto-fit mode (default "220px")
 *   list_class, id  string  optional
 */
class card_mini_icon_and_text
{
    private const VARIANTS = ['row', 'stack'];
    private const CARD_FIELDS = ['svg', 'text', 'link', 'target', 'img', 'variant'];

    public static function render(array $data = []): string
    {
        if (is_array($data['items'] ?? null)) return self::render_list($data);
        return self::render_card($data);
    }

    public static function render_card(array $card): string
    {
        $text = self::str($card['text'] ?? $card['title'] ?? '');
        $icon = self::str($card['svg'] ?? $card['icon'] ?? '');

        $iconHtml = $icon !== ''
            ? trim((string) PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => 'card-mini-svg']))
            : '';
        if ($text === '' && $iconHtml === '') return '';

        $img = self::str($card['img'] ?? $card['feature_image'] ?? '');
        $imageHtml = $img !== ''
            ? trim((string) PlatformComponentRenderer::render('image', ['src' => $img, 'alt' => '', 'class' => 'card-mini-bg', 'lazy' => true]))
            : '';

        $variant = strtolower(self::str($card['variant'] ?? ''));
        if (!in_array($variant, self::VARIANTS, true)) $variant = 'row';

        $link = self::link($card['link'] ?? '');
        $attrs = '';
        if ($link !== '') {
            $attrs .= ' href="' . self::e($link) . '"';
            $target = self::str($card['target'] ?? '');
            if ($target !== '') {
                $attrs .= ' target="' . self::e($target) . '"' . ($target === '_blank' ? ' rel="noopener"' : '');
            }
        }

        $class = self::str($card['class'] ?? '');

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'tag' => $link !== '' ? 'a' : 'div',
            'variant' => $variant,
            'extra_class' => ($imageHtml !== '' ? ' card-mini-has-image' : '') . ($class !== '' ? ' ' . self::e($class) : ''),
            'attrs' => $attrs,
            'image' => $imageHtml,
            'icon' => $iconHtml !== '' ? '<span class="card-mini-icon">' . $iconHtml . '</span>' : '',
            'text' => $text !== '' ? '<span class="card-mini-text">' . self::e($text) . '</span>' : '',
        ]);
    }

    private static function render_list(array $data): string
    {
        $defaults = array_intersect_key($data, array_flip(self::CARD_FIELDS));
        unset($defaults['link'], $defaults['text'], $defaults['svg']);

        $cards = [];
        foreach ($data['items'] as $item) {
            if (!is_array($item)) continue;
            $html = self::render_card(array_merge($defaults, $item));
            if ($html !== '') $cards[] = '<li>' . $html . '</li>';
        }
        if (empty($cards)) return '';

        $columns = (int) ($data['columns'] ?? 0);
        $gridAttrs = '';
        $style = '';
        if ($columns > 0) {
            $gridAttrs = (string) PlatformComponentRenderer::value('utility_grid_listing', ['count' => count($cards), 'max' => $columns]);
        } else {
            $minWidth = self::str($data['min_width'] ?? '');
            if ($minWidth !== '' && !preg_match('/[;{}<>"\']/', $minWidth)) {
                $style = ' style="--card-mini-min-width: ' . self::e($minWidth) . ';"';
            }
        }

        $class = 'card-mini-listing' . ($columns > 0 ? '' : ' card-mini-listing-auto');
        $listClass = self::str($data['list_class'] ?? $data['class_list'] ?? '');
        if ($listClass !== '') $class .= ' ' . self::e($listClass);
        $id = self::str($data['id'] ?? '');

        return '<ul class="' . $class . '"' . ($id !== '' ? ' id="' . self::e($id) . '"' : '') . $gridAttrs . $style . '>'
            . implode('', $cards) . '</ul>';
    }

    private static function link(mixed $link): string
    {
        if (is_string($link)) return trim($link);
        if (is_array($link)) return button::resolve_link(['link' => $link]);
        return '';
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
