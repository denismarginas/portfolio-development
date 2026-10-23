<?php

/**
 * Popup trigger: clicking the trigger opens the content in the popup (see popup.js).
 *
 *   PlatformComponentRenderer::render('popup', [
 *       'trigger' => '<button ...>View</button>',   html of the clickable element (a link works too)
 *       'content' => '<div>...</div>',              html shown in the popup
 *   ]);
 *
 * $data:
 *   trigger  string  clickable html (also: trigger_html)
 *   content  string  popup html (also: content_html); ignored when "items" is given
 *   content_component  object  { "component": "form", "data": { ... } } rendered as the content
 *                      (instead of "content"), so a popup can show any component, also from JSON
 *   items    array   gallery: html strings or ['src' => .., 'alt' => ..] images
 *   class    string  extra classes on the trigger wrapper
 *   hash     string  #hash that opens this popup from the URL (set on open, removed on close)
 *   panel    bool    wrap the content in a scrollable .popup-panel (forms, cards, text), default false
 *   width    string  panel width (CSS value, e.g. "720px"), default 640px
 *   inline   bool    the wrapper takes no box of its own (display: contents), for links in lists / text
 *
 * Open an element that is already on the page (moved into the popup, back on close - nothing copied):
 *   '<div class="..."' . popup::open_attrs(['hash' => 'contact-form', 'panel' => true]) . '>'
 *   -> #contact-form in the URL (or a link to it) opens that element in the popup.
 *   The component using it lists "popup" in its dependencies (so this class is loaded).
 */
class popup
{
    public static function render(array $data = []): string
    {
        $contentHtml = (string) ($data['content'] ?? $data['content_html'] ?? '');
        if ($contentHtml === '' && is_array($data['content_component'] ?? null)) {
            $contentHtml = self::component($data['content_component']);
        }
        $triggerHtml = (string) ($data['trigger'] ?? $data['trigger_html'] ?? '');
        if ($contentHtml === '' && $triggerHtml === '') return '';

        $gallery = is_array($data['items'] ?? null) ? $data['items'] : [];
        $inner = !empty($gallery) ? self::gallery($gallery) : self::panel($contentHtml, $data);

        $class = trim((string) ($data['class'] ?? ''));
        if (($data['inline'] ?? false) === true) $class = trim('popup-trigger-inline ' . $class);

        $hash = trim((string) ($data['hash'] ?? ''), " #");

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/parts/trigger.html', [
            'trigger' => $triggerHtml,
            'content' => $inner,
            'class' => self::e($class),
            'attrs' => $hash !== '' ? ' data-popup-hash="' . self::e($hash) . '"' : '',
        ]);
    }

    /**
     * Attributes that make an element of the page open in the popup by #hash (popup.js data-popup-move).
     * $options: hash (required), panel (bool, default true), width (CSS value), move (selector, default: the element itself)
     */
    public static function open_attrs(array $options): string
    {
        $hash = trim((string) ($options['hash'] ?? ''), " #");
        if ($hash === '') return '';

        $attrs = ' data-popup-hash="' . self::e($hash) . '" data-popup-move="' . self::e(trim((string) ($options['move'] ?? ''))) . '"';
        if (($options['panel'] ?? true) !== false) $attrs .= ' data-popup-panel';

        $width = trim((string) ($options['width'] ?? ''));
        if ($width !== '' && preg_match('/^[a-z0-9.%()+\-\s,]+$/i', $width)) $attrs .= ' data-popup-width="' . self::e($width) . '"';

        return $attrs;
    }

    /** { component, data } -> html of that component. */
    private static function component(array $block): string
    {
        $name = trim((string) ($block['component'] ?? ''));
        if ($name === '') return '';
        return (string) PlatformComponentRenderer::render($name, is_array($block['data'] ?? null) ? $block['data'] : []);
    }

    /** Gallery mode: one slide per item (utility_slider in the popup). */
    private static function gallery(array $items): string
    {
        $slides = '';
        foreach ($items as $item) {
            $slides .= '<div class="popup-slide">' . (is_array($item)
                ? PlatformComponentRenderer::render('image', ['src' => $item['src'] ?? '', 'alt' => $item['alt'] ?? ''])
                : (string) $item) . '</div>';
        }
        return '<div class="popup-gallery" data-popup-gallery>' . $slides . '</div>';
    }

    private static function panel(string $html, array $data): string
    {
        if (($data['panel'] ?? false) !== true || $html === '') return $html;

        $width = trim((string) ($data['width'] ?? ''));
        $style = $width !== '' && preg_match('/^[a-z0-9.%()+\-\s,]+$/i', $width)
            ? ' style="--popup-panel-width: ' . self::e($width) . ';"'
            : '';

        return '<div class="popup-panel"' . $style . '>' . $html . '</div>';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
