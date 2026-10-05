<?php

/**
 * Sort row + toggle button for a filters form.
 *
 *   filters_sort::render([...])         -> sort row HTML ("" when there are no fields)
 *   filters_sort::toggle_button($data)  -> sort icon button ("" when there are no fields)
 *   filters_sort::values($item, $data)  -> sort values for the JSON index ("_sort")
 *
 * $data:
 *   target          string  id of the listing <ul> (required)
 *   sort_fields     array   [{ "label": "Name", "by": "name", "type": "text|number|date", "order": "asc|desc",
 *                             "order_labels": { "asc": "A-Z", "desc": "Z-A" } }, ...]
 *                           "by" = item key ("name") or post path ("seo.title", "date.publish" = data.date.publish)
 *   sort_default    string  label of a first "keep the original order" option (e.g. "Default"); omit for none
 *   sort_expanded   bool    sort row open on load (default false)
 *   sort_by_label, sort_order_label   select labels (default "Sort by:", "Order:")
 *
 * Default order labels: text A-Z / Z-A, number Low-High / High-Low, date Oldest / Newest.
 * Without "sort_default" the first field (and its order) is applied on load.
 */
class filters_sort
{
    private const ORDER_LABELS = [
        'text' => ['asc' => 'A-Z', 'desc' => 'Z-A'],
        'number' => ['asc' => 'Low-High', 'desc' => 'High-Low'],
        'date' => ['asc' => 'Oldest', 'desc' => 'Newest'],
    ];

    public static function render(array $data = []): string
    {
        $target = trim((string) ($data['target'] ?? ''));
        $fields = self::fields($data);
        if ($target === '' || empty($fields)) return '';

        $default = trim((string) ($data['sort_default'] ?? ''));
        $options = $default !== '' ? '<option value="" data-type="" data-order="">' . self::e($default) . '</option>' : '';
        foreach ($fields as $field) {
            $options .= '<option value="' . self::e($field['by']) . '"'
                . ' data-type="' . self::e($field['type']) . '"'
                . ' data-order="' . self::e($field['order']) . '"'
                . ' data-label-asc="' . self::e($field['labels']['asc']) . '"'
                . ' data-label-desc="' . self::e($field['labels']['desc']) . '">'
                . self::e($field['label']) . '</option>';
        }

        // Order select starts as the first option: disabled for "Default", else the first field's order.
        $first = $default !== '' ? null : $fields[0];
        $labels = $first['labels'] ?? self::ORDER_LABELS['text'];

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'target' => self::e($target),
            'by_id' => self::e($target . '-sort-by'),
            'order_id' => self::e($target . '-sort-order'),
            'by_label' => self::e((string) ($data['sort_by_label'] ?? 'Sort by:')),
            'order_label' => self::e((string) ($data['sort_order_label'] ?? 'Order:')),
            'by_options' => $options,
            'asc_label' => self::e($labels['asc']),
            'desc_label' => self::e($labels['desc']),
            'desc_selected' => ($first['order'] ?? 'asc') === 'desc' ? ' selected' : '',
            'order_disabled' => $first === null ? ' disabled' : '',
        ]);
    }

    public static function toggle_button(array $data = []): string
    {
        if (empty(self::fields($data))) return '';
        $expanded = ($data['sort_expanded'] ?? false) === true ? 'true' : 'false';
        $icon = (string) PlatformComponentRenderer::render('svg', ['icon' => 'sort', 'class' => 'filters-icon']);
        return '<button type="button" class="icon toggle-sort" aria-expanded="' . $expanded . '" aria-label="Sort">' . $icon . '</button>';
    }

    /** ' hidden' when the sort row starts closed. */
    public static function hidden_attr(array $data = []): string
    {
        return ($data['sort_expanded'] ?? false) === true ? '' : ' hidden';
    }

    /** { "seo.title": "...", "date.publish": 1556668800 } for one item / post. */
    public static function values(array $item, array $data): array
    {
        $out = [];
        foreach (self::fields($data) as $field) {
            $value = self::path($item, $field['by']);
            if (is_array($value)) $value = reset($value);
            if (!is_scalar($value) || trim((string) $value) === '') {
                $out[$field['by']] = null;
                continue;
            }
            $value = trim((string) $value);
            if ($field['type'] === 'number') {
                $out[$field['by']] = preg_match('/-?\d+(?:[.,]\d+)?/', $value, $m) ? (float) str_replace(',', '.', $m[0]) : null;
            } elseif ($field['type'] === 'date') {
                $time = strtotime($value);
                $out[$field['by']] = $time === false ? null : $time;
            } else {
                $out[$field['by']] = $value;
            }
        }
        return $out;
    }

    /** Normalized fields: by, label, type, order, labels. */
    public static function fields(array $data): array
    {
        $raw = $data['sort_fields'] ?? [];
        if (!is_array($raw)) return [];

        $fields = [];
        foreach ($raw as $field) {
            if (!is_array($field)) continue;
            $by = trim((string) ($field['by'] ?? ''));
            if ($by === '') continue;
            $type = strtolower((string) ($field['type'] ?? 'text'));
            if (!isset(self::ORDER_LABELS[$type])) $type = 'text';
            $labels = array_merge(self::ORDER_LABELS[$type], array_map('strval', (array) ($field['order_labels'] ?? [])));
            $fields[] = [
                'by' => $by,
                'label' => (string) ($field['label'] ?? ucfirst($by)),
                'type' => $type,
                'order' => strtolower((string) ($field['order'] ?? ($type === 'text' ? 'asc' : 'desc'))) === 'desc' ? 'desc' : 'asc',
                'labels' => $labels,
            ];
        }
        return $fields;
    }

    /** "name" -> $item['name']; "seo.title" -> $item['seo']['title'] or $item['data']['seo']['title']. */
    private static function path(array $item, string $path): mixed
    {
        foreach ([$item, $item['data'] ?? null] as $source) {
            $value = $source;
            foreach (explode('.', ltrim($path, '@')) as $key) {
                if (!is_array($value) || !array_key_exists($key, $value)) { $value = null; break; }
                $value = $value[$key];
            }
            if ($value !== null) return $value;
        }
        return null;
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
