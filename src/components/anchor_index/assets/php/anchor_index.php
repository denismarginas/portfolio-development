<?php

/**
 * Links to sections of the page ("on this page"). The section in view gets aria-current="true".
 *
 *   {
 *     "component": "anchor_index",
 *     "data": {
 *       "title": "Sections",
 *       "items": [
 *         { "id": "web", "text": "Website", "date": "2025-05-01" },
 *         { "id": "web-content", "text": "Web Content" },
 *         { "id": "numberinputcustomizer", "text": "Number Input Customizer" }
 *       ]
 *     }
 *   }
 *
 * data:
 *   title        string  optional heading of the list
 *   items        array   { "id" (id of the section on the page), "text", "date" (optional, "YYYY-MM-DD") }
 *                        items without id or text are skipped
 *   date_format  string  PlatformTextService::format_date format (default "j F, Y" -> "1 May, 2025")
 *   min_items    int     the list is hidden when fewer of its sections are on the page (default 2)
 *   sticky       bool    the list sticks below the header while the page scrolls (default false - e.g. a
 *                        section_layout_aside aside is already sticky); long lists scroll inside
 *   class, id    string  optional
 *
 * Only the listed sections are tracked: while you scroll through other blocks, the last listed section
 * you passed stays marked (anchor_index.js). Links whose id is not on the page (e.g. a gallery without
 * images) are hidden by the script, and the whole list when fewer than min_items are left.
 */
class anchor_index
{
    public static function render(array $data = []): string
    {
        $format = self::str($data['date_format'] ?? '') ?: 'j F, Y';
        $items = '';
        foreach ((array) ($data['items'] ?? []) as $item) {
            if (!is_array($item)) continue;
            $target = trim(self::str($item['id'] ?? ''), '# ');
            $text = self::str($item['text'] ?? '');
            if ($target === '' || $text === '') continue;
            $date = self::str($item['date'] ?? '');

            $items .= '<li><a class="anchor-index-link" href="#' . self::e($target) . '">'
                . ($date !== '' ? '<span class="anchor-index-date">' . self::e(PlatformTextService::format_date($date, $format)) . '</span>' : '')
                . '<span class="anchor-index-text">' . self::e($text) . '</span></a></li>';
        }
        if ($items === '') return '';

        $title = self::str($data['title'] ?? '');
        $classes = [];
        if (($data['sticky'] ?? false) === true) $classes[] = 'anchor-index-sticky';
        if (($class = self::str($data['class'] ?? '')) !== '') $classes[] = $class;
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render([
            'extra_class' => $classes !== [] ? ' ' . self::e(implode(' ', $classes)) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'label' => self::e($title !== '' ? $title : 'On this page'),
            'min' => (string) max(1, (int) ($data['min_items'] ?? 2)),
            'title' => $title !== '' ? '<span class="anchor-index-title">' . self::e($title) . '</span>' : '',
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
