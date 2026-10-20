<?php

/**
 * Simple text list with an svg icon before every item.
 *
 *   { "component": "icon_list", "data": { "items": ["Ryzen 5 7600X", "32GB DDR5"] } }
 *   { "component": "icon_list", "data": { "items": ["PHP", "SCSS"], "svg": "check", "layout": "inline", "icon_style": "plain" } }
 *   { "component": "icon_list", "data": { "items": [ { "text": "Email", "svg": "mail" }, "Phone" ] } }
 *
 * data:
 *   items       array   strings, or objects { "text", "svg" (this item's icon, "" = none) }
 *   svg         string  icon for all items, from src/content/svg (default "chevron-right"; "" = no icon)
 *   layout      string  "list" (default, one under another) | "inline" (in a row that wraps)
 *   icon_style  string  "badge" (default, icon in a primary-colored circle) | "plain" (just the icon)
 *   size        string  "md" (default, 14px text) | "sm" (12px) | "base" (16px); the icon follows the text size
 *   class, id   string  optional
 */
class icon_list
{
    private const DEFAULT_SVG = 'chevron-right';

    public static function render(array $data = []): string
    {
        $svg = array_key_exists('svg', $data) ? self::str($data['svg']) : self::DEFAULT_SVG;
        $icons = [];

        $items = '';
        foreach ((array) ($data['items'] ?? []) as $item) {
            $text = self::str(is_array($item) ? ($item['text'] ?? '') : $item);
            if ($text === '') continue;
            $name = is_array($item) && array_key_exists('svg', $item) ? self::str($item['svg']) : $svg;
            if ($name !== '' && !isset($icons[$name])) {
                $icons[$name] = (string) PlatformComponentRenderer::render('svg', ['icon' => $name, 'class' => 'icon-list-icon']);
            }
            $items .= '<li class="icon-list-item">' . ($name !== '' ? $icons[$name] : '')
                . '<span class="icon-list-text">' . self::e($text) . '</span></li>';
        }
        if ($items === '') return '';

        $classes = [];
        if (self::str($data['layout'] ?? '') === 'inline') $classes[] = 'icon-list-inline';
        if (self::str($data['icon_style'] ?? '') === 'plain') $classes[] = 'icon-list-plain';
        if (in_array($size = self::str($data['size'] ?? ''), ['sm', 'base'], true)) $classes[] = 'icon-list-' . $size;
        if (($class = self::str($data['class'] ?? '')) !== '') $classes[] = $class;
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render([
            'extra_class' => $classes !== [] ? ' ' . self::e(implode(' ', $classes)) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'items' => $items,
        ]);
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
