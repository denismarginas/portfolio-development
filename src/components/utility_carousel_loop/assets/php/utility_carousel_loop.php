<?php

/**
 * Infinite-loop row of any items (the engine behind carousel_projects_items and taxonomy_marquee).
 *
 *   PlatformComponentRenderer::render('utility_carousel_loop', [
 *       'items' => ['<span>One</span>', '<span>Two</span>'],          // html strings, or
 *       'items' => [['html' => '...', 'attrs' => ' data-x="1"', 'class' => 'my-item']],
 *       'class' => 'taxonomy-marquee-loop',
 *       'speed' => 'normal',
 *   ]);
 *
 * $data:
 *   items           array   html strings or { html, attrs (raw, leading space), class }; empty ones are skipped
 *   class           string  extra classes on the root (e.g. "carousel-projects-items")
 *   list_class      string  extra classes on the list
 *   item_class      string  extra classes on every item
 *   list_tag        string  "ul" (default, items are <li>) | "div" (items are <div>, e.g. aria-hidden decoration)
 *   attrs           string  extra raw attributes on the root (leading space), e.g. ' aria-hidden="true"'
 *   direction       string  "left" (default, the row moves to the left) | "right"
 *   speed           string  "slow" (80s) | "normal" (50s) | "fast" (30s) | a duration like "60s" (default "slow")
 *   pause_on_hover  bool    stop while the mouse is over it (default true)
 *   gap, gap_phone  string  space between items: a length, a number (= px), "--dm-spacing-sm" or "var(--x)"
 *   style           string  extra CSS declarations for the root style attribute (e.g. size variables)
 *
 * The JS (utility_carousel_loop.js) duplicates the items once and sets data-animated="true";
 * the list then moves by exactly one set (half its width + half a gap) and starts over.
 * window.CarouselLoop.init(element) sets up a loop added later.
 */
class utility_carousel_loop
{
    public const SPEEDS = ['slow' => '80s', 'normal' => '50s', 'fast' => '30s'];

    public static function render(array $data = []): string
    {
        $listTag = ($data['list_tag'] ?? 'ul') === 'div' ? 'div' : 'ul';
        $itemTag = $listTag === 'ul' ? 'li' : 'div';
        $itemClass = self::classes($data['item_class'] ?? '');

        $items = '';
        foreach ((array) ($data['items'] ?? []) as $item) {
            $html = is_array($item) ? (string) ($item['html'] ?? '') : (string) $item;
            if (trim($html) === '') continue;
            $attrs = is_array($item) ? (string) ($item['attrs'] ?? '') : '';
            $class = 'carousel-loop-item' . $itemClass . (is_array($item) ? self::classes($item['class'] ?? '') : '');
            $items .= '<' . $itemTag . ' class="' . $class . '"' . $attrs . '>' . $html . '</' . $itemTag . '>';
        }
        if ($items === '') return '';

        return PlatformTemplateRenderer::render([
            'extra_class' => self::classes($data['class'] ?? ''),
            'list_class' => self::classes($data['list_class'] ?? ''),
            'list_tag' => $listTag,
            'direction' => ($data['direction'] ?? 'left') === 'right' ? 'right' : 'left',
            'pause_on_hover' => self::flag($data['pause_on_hover'] ?? true) ? 'true' : 'false',
            'attrs' => (string) ($data['attrs'] ?? ''),
            'style' => self::style($data),
            'items' => $items,
        ]);
    }

    /** "slow" | "normal" | "fast" | "60s" / "1500ms" -> CSS duration (default slow). */
    public static function duration(mixed $speed): string
    {
        $speed = is_scalar($speed) ? strtolower(trim((string) $speed)) : '';
        if (isset(self::SPEEDS[$speed])) return self::SPEEDS[$speed];
        return preg_match('/^\d+(\.\d+)?m?s$/', $speed) ? $speed : self::SPEEDS['slow'];
    }

    /** "--dm-spacing-lg" -> "var(--dm-spacing-lg)"; "var(--x)" kept; a number -> px; a length kept; else ''. */
    public static function gap(mixed $value): string
    {
        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric(trim($value)))) {
            return ((float) $value) . 'px';
        }
        if (!is_string($value)) return '';
        $value = trim($value);
        if (preg_match('/^--[a-z0-9-]+$/i', $value)) return 'var(' . $value . ')';
        if (preg_match('/^var\(--[a-z0-9-]+\)$/i', $value)) return $value;
        return preg_match('/^\d+(\.\d+)?(px|rem|em|vw|vh|%)$/i', $value) ? strtolower($value) : '';
    }

    private static function style(array $data): string
    {
        $css = '--carousel-duration: ' . self::duration($data['speed'] ?? 'slow') . ';';
        foreach (['gap' => '--carousel-gap', 'gap_phone' => '--carousel-gap-phone'] as $key => $var) {
            $gap = self::gap($data[$key] ?? null);
            if ($gap !== '') $css .= ' ' . $var . ': ' . $gap . ';';
        }
        $extra = trim((string) ($data['style'] ?? ''));
        if ($extra !== '' && !preg_match('/[<>"{}]/', $extra)) $css .= ' ' . $extra;

        return ' style="' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '"';
    }

    private static function classes(mixed $value): string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';
        return $value !== '' ? ' ' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '';
    }

    /** true/false, also "true"/"false", "1"/"0", "yes"/"no", "on"/"off". */
    private static function flag(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        if (is_int($value) || is_float($value)) return $value != 0;
        return !in_array(strtolower(trim((string) $value)), ['false', '0', 'no', 'off', ''], true);
    }
}
