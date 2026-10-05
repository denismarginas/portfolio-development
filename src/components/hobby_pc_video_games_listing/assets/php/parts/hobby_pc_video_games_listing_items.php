<?php

/**
 * The games shown by the listing (also used by hobby_pc_video_games_filters, so both
 * components always work on the same items).
 *
 * $data:
 *   type          string  item file (default "hobby_pc_video_game" -> data_items_hobby_pc_video_game.json)
 *   items         array   items passed directly (skips the file)
 *   show_hidden   bool    also return items with "display": false (default false;
 *                         the listing passes "render_hidden" here so they can be toggled by the Display filter)
 *   sort          array   one rule or a list: { "by": "name|rank|playtime|<key>", "order": "asc|desc", "type": "text|number" }
 *                         (default: name asc). Next rules break ties.
 *   max_items     int     limit (default: all)
 */
class hobby_pc_video_games_listing_items
{
    public const DEFAULT_TYPE = 'hobby_pc_video_game';
    private const NUMBER_KEYS = ['rank', 'playtime'];

    public static function find(array $data): array
    {
        $items = $data['items'] ?? PlatformDataService::get_all_items_from_file(self::type($data)) ?? [];
        if (!is_array($items)) return [];

        $showHidden = ($data['show_hidden'] ?? false) === true;
        $items = array_values(array_filter($items, fn ($item): bool =>
            is_array($item)
            && self::id($item) !== ''
            && ($item['settings']['render'] ?? true) !== false
            && ($showHidden || !self::is_hidden($item))
        ));

        $items = self::sort($items, $data['sort'] ?? ['by' => 'name', 'order' => 'asc']);

        $max = (int) ($data['max_items'] ?? 0);
        return $max > 0 ? array_slice($items, 0, $max) : $items;
    }

    /** "display": false -> hidden by default (shown with Display: All). Missing = true. */
    public static function is_hidden(array $item): bool
    {
        return ($item['display'] ?? true) === false;
    }

    public static function type(array $data): string
    {
        $type = trim((string) ($data['type'] ?? $data['item_type'] ?? ''));
        return $type !== '' ? $type : self::DEFAULT_TYPE;
    }

    public static function id(array $item): string
    {
        return trim((string) ($item['_id'] ?? $item['item_id'] ?? ''));
    }

    /** "4320h+" -> 4320, " 7" -> 7, "" -> null */
    public static function number(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) return (float) $value;
        if (!is_string($value)) return null;
        return preg_match('/-?\d+(?:[.,]\d+)?/', $value, $m) ? (float) str_replace(',', '.', $m[0]) : null;
    }

    /** "ARPG, Singleplayer" -> ["ARPG", "Singleplayer"] */
    public static function tags(array $item): array
    {
        $tags = $item['tags'] ?? [];
        if (is_string($tags)) $tags = explode(',', $tags);
        if (!is_array($tags)) return [];
        $tags = array_map(fn ($t) => trim((string) $t), array_filter($tags, 'is_scalar'));
        return array_values(array_unique(array_filter($tags, fn ($t) => $t !== '')));
    }

    private static function sort(array $items, mixed $rules): array
    {
        if (!is_array($rules)) return $items;
        $rules = array_is_list($rules) ? $rules : [$rules];
        $rules = array_values(array_filter($rules, fn ($r) => is_array($r) && trim((string) ($r['by'] ?? '')) !== ''));
        if (count($items) < 2 || empty($rules)) return $items;

        usort($items, function (array $a, array $b) use ($rules): int {
            foreach ($rules as $rule) {
                $cmp = self::compare($a, $b, $rule);
                if ($cmp !== 0) return $cmp;
            }
            return 0;
        });
        return $items;
    }

    private static function compare(array $a, array $b, array $rule): int
    {
        $key = (string) $rule['by'];
        $dir = strtolower((string) ($rule['order'] ?? 'asc')) === 'desc' ? -1 : 1;
        $type = strtolower((string) ($rule['type'] ?? (in_array($key, self::NUMBER_KEYS, true) ? 'number' : 'text')));

        if ($type === 'number') {
            $va = self::number($a[$key] ?? null);
            $vb = self::number($b[$key] ?? null);
            if ($va === null || $vb === null) return ($va === null) <=> ($vb === null); // empty last
            return $dir * ($va <=> $vb);
        }

        $va = trim((string) (is_scalar($a[$key] ?? null) ? $a[$key] : ''));
        $vb = trim((string) (is_scalar($b[$key] ?? null) ? $b[$key] : ''));
        if ($va === '' || $vb === '') return ($va === '') <=> ($vb === '');
        return $dir * strnatcasecmp($va, $vb);
    }
}
