<?php

/**
 * Entrance animations when elements scroll into view (transitions.js).
 *
 * Preferred, no extra HTML: "transition" in the data of ANY component puts the attributes on its own root element
 * (PlatformComponentRenderer::apply_transition); inside "columns" it goes on that <div class="column">:
 *   { "component": "taxonomy_listing", "data": { "transition": "fade slide-bottom", … } }
 *   items inside a component: { "transition": { "effect": "fade slide-bottom", "target": ".taxonomy-item", "stagger": 150 } }
 *   { "component": "column", "data": { "transition": { "effect": "fade slide-right", "duration": 900, "delay": 150 } }, "children": [ … ] }
 *
 * This component is the wrapper version (adds a <div class="transitions">), for a group of blocks that should
 * animate together or one after the other (target children + stagger):
 *   {
 *     "component": "transitions",
 *     "data": { "effect": "fade slide-bottom", "duration": 800, "delay": 100 },
 *     "children": [ { "component": "block_title", "data": { … } } ]
 *   }
 *
 * Each child one after the other:
 *   { "component": "transitions", "data": { "effect": "fade zoom", "target": "children", "stagger": 120 }, "children": [ … ] }
 *
 * data:
 *   effect     string|array  fade, slide-left, slide-right, slide-top, slide-bottom, rotate (rotate-left), rotate-right,
 *                            zoom (zoom-in), zoom-out, blur; combine with spaces ("fade slide-left") or an array.
 *                            slide-* = comes in FROM that side.
 *   duration   int|string    ms (800) or "0.8s" (default 700)
 *   delay      int|string    ms or "0.2s" (default 0)
 *   easing     string        CSS easing (default cubic-bezier(0.2, 0.7, 0.2, 1))
 *   distance   string        slide distance, "60px", "20%" (default 40px)
 *   angle      string        rotate angle, "20deg" (default 12deg)
 *   scale      number        zoom start scale (default 0.85, zoom-out 1.15)
 *   blur       string        blur start, "12px" (default 10px)
 *   threshold  number        0–1 visible part that starts it (default 0.15)
 *   once       bool          false = plays again each time it re-enters the view (default true)
 *   target     string        animate elements inside instead of the wrapper: "children" (direct children)
 *                            or a CSS selector, e.g. ".taxonomy-item"
 *   stagger    int           with a target: extra delay per item, ms
 *   each       bool          with a target: each item starts when it scrolls into view (long lists)
 *   contents   bool          wrapper without its own box (display: contents), e.g. inside a grid; use with target children
 *   tag        string        wrapper tag (default div)
 *   class, id  string        optional
 *
 * Without the component: put the same options as attributes on any element, e.g.
 *   <div data-transition="fade slide-left" data-transition-duration="900">…</div>
 */
class transitions
{
    private const EFFECTS = [
        'fade', 'slide-left', 'slide-right', 'slide-top', 'slide-bottom',
        'rotate', 'rotate-left', 'rotate-right', 'zoom', 'zoom-in', 'zoom-out', 'blur',
    ];

    /** data key => data-transition-* attribute */
    private const OPTIONS = [
        'duration' => 'duration', 'delay' => 'delay', 'easing' => 'easing', 'distance' => 'distance',
        'angle' => 'angle', 'scale' => 'scale', 'blur' => 'blur', 'threshold' => 'threshold',
        'target' => 'target', 'stagger' => 'stagger',
    ];

    public static function render(array $data = []): string
    {
        $children = section::render_children($data);
        if (trim($children) === '') return '';

        $effects = self::effects($data['effect'] ?? $data['effects'] ?? 'fade');
        $attrs = $effects !== '' ? self::attr('data-transition', $effects) : '';
        foreach (self::OPTIONS as $key => $name) {
            if (!isset($data[$key]) || !is_scalar($data[$key]) || trim((string) $data[$key]) === '') continue;
            $attrs .= self::attr('data-transition-' . $name, (string) $data[$key]);
        }
        if (($data['once'] ?? true) === false) $attrs .= self::attr('data-transition-once', 'false');
        if (($data['each'] ?? false) === true) $attrs .= self::attr('data-transition-each', 'true');

        $tag = strtolower(trim((string) ($data['tag'] ?? 'div')));
        if (!preg_match('/^[a-z][a-z0-9-]*$/', $tag)) $tag = 'div';

        $class = trim((string) ($data['class'] ?? ''));
        if (($data['contents'] ?? false) === true) $class = trim('transitions-contents ' . $class);
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'tag' => $tag,
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? self::attr('id', $id) : '',
            'attrs' => $attrs,
            'children' => $children,
        ]);
    }

    /** "fade Slide-Left foo" / ["fade", "zoom"] -> "fade slide-left" (known effects only). */
    private static function effects(mixed $value): string
    {
        $list = is_array($value) ? $value : preg_split('/[\s,]+/', (string) $value);
        $out = [];
        foreach ($list as $effect) {
            $effect = strtolower(trim((string) $effect));
            if (in_array($effect, self::EFFECTS, true) && !in_array($effect, $out, true)) $out[] = $effect;
        }
        return implode(' ', $out);
    }

    private static function attr(string $name, string $value): string
    {
        return ' ' . $name . '="' . self::e($value) . '"';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
