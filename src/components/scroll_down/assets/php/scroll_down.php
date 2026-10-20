<?php

/**
 *   {
 *     "component": "scroll_down",
 *     "data": {
 *       "target": "",
 *       "position": "bottom",
 *       "hide_mobile": true
 *     }
 *   }
 *
 * A scroll-down cue (small mouse with a moving wheel). On click it scrolls to the content right after
 * its section, or to "target" when set.
 *
 * data:
 *   target       string  CSS selector to scroll to (e.g. "#about"); empty (default) = the end of the closest
 *                        <section> (or [data-scroll-down-scope]) around the button
 *   label        string  aria-label (default "Scroll down")
 *   position     string  "bottom" (default, absolute at the bottom center of the parent, which needs position: relative)
 *                        | "inline" (stays in the normal flow)
 *   hide_mobile  bool    hide on screens <= md (default true)
 *   class, id    string  optional
 *
 * Color: set --scroll-down-color on a parent (default currentColor).
 */
class scroll_down
{
    public static function render(array $data = []): string
    {
        $target = is_scalar($data['target'] ?? null) ? trim((string) $data['target']) : '';
        $label = is_scalar($data['label'] ?? null) && trim((string) $data['label']) !== '' ? trim((string) $data['label']) : 'Scroll down';
        $position = strtolower((string) ($data['position'] ?? 'bottom')) === 'inline' ? 'inline' : 'bottom';
        $hideMobile = ($data['hide_mobile'] ?? true) !== false;
        $class = is_scalar($data['class'] ?? null) ? trim((string) $data['class']) : '';
        $id = is_scalar($data['id'] ?? null) ? trim((string) $data['id']) : '';

        $attrs = ' aria-label="' . self::e($label) . '"'
            . ' data-position="' . $position . '"'
            . ($hideMobile ? ' data-hide-mobile' : '')
            . ($target !== '' ? ' data-scroll-down-target="' . self::e($target) . '"' : '')
            . ($id !== '' ? ' id="' . self::e($id) . '"' : '');

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'attrs' => $attrs,
        ]);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
