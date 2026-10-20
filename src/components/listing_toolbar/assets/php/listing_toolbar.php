<?php

/**
 * Toolbar settings from data_items_listing_toolbar.json, one item per toolbar:
 *
 *   {
 *     "_id": "pc-video-games",
 *     "settings": { "render": true },
 *     "data": {
 *       "search":  { "placeholder", "button", "label", "toggle", "expanded", "min_chars" },
 *       "filters": { "label", "expanded", "hide_single_option", "grid_max", "column_balancing", "items": [ filter definitions ] },
 *       "sort":    { "default", "expanded", "by_label", "order_label", "grid_max", "column_balancing", "fields": [ ... ] },
 *       "results": { "text" ("{n}" = count), "label", "hint", "no_results", "show_all" }
 *     }
 *   }
 *
 * listing_toolbar::data($data, 'pc-video-games') returns the component data with every group key as
 * "{group}_{key}" (search_placeholder, filters_expanded, sort_fields, results_text, ...), plus
 * "filter_items" (filters.items) and "hide_single_option". The page picks the toolbar with "toolbar": "<_id>"
 * and any key set on the page itself (e.g. "filters_exclude", "sort_expanded") wins over the toolbar.
 */
class listing_toolbar
{
    public const FILE = 'listing_toolbar';
    private const GROUPS = ['search', 'filters', 'sort', 'results'];
    private const SPECIAL = [
        'filters.items' => 'filter_items',
        'filters.hide_single_option' => 'hide_single_option',
    ];

    public static function data(array $data, string $defaultId): array
    {
        $id = trim((string) ($data['toolbar'] ?? '')) ?: $defaultId;
        return array_merge(self::flatten(self::find($id)), $data);
    }

    /** Toolbar "data" by _id ([] when missing or "render": false). */
    public static function find(string $id): array
    {
        foreach (PlatformDataService::get_all_items_from_file(self::FILE) ?? [] as $item) {
            if (!is_array($item) || (string) ($item['_id'] ?? '') !== $id) continue;
            if (($item['settings']['render'] ?? true) === false) return [];
            return is_array($item['data'] ?? null) ? $item['data'] : [];
        }
        return [];
    }

    /** Filter definitions of the merged data. */
    public static function filters(array $data): array
    {
        return array_values(array_filter((array) ($data['filter_items'] ?? []), 'is_array'));
    }

    /** Search mode of a filter ("json-date-search-mode" is the old name). */
    public static function mode(array $def, string $default = ''): string
    {
        return (string) ($def['json-search-mode'] ?? $def['json-date-search-mode'] ?? $default);
    }

    private static function flatten(array $toolbar): array
    {
        $out = [];
        foreach (self::GROUPS as $group) {
            if (!is_array($toolbar[$group] ?? null)) continue;
            foreach ($toolbar[$group] as $key => $value) {
                $out[self::SPECIAL[$group . '.' . $key] ?? $group . '_' . $key] = $value;
            }
        }
        return $out;
    }
}
