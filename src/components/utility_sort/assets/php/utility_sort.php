<?php

require_once __DIR__ . '/parts/utility_sort_path.php';
require_once __DIR__ . '/parts/utility_sort_date.php';
require_once __DIR__ . '/parts/utility_sort_group.php';
require_once __DIR__ . '/parts/utility_sort_text.php';

/**
 * Sorts items by a list of rules: the first rule wins, the next ones break ties.
 * Full ties keep the original order.
 *
 * params:
 *   items  array         posts / items to sort
 *   rules  array|object  one rule or a list of rules
 *
 * Rules ("by" is a path; "project.types" = data.project.types):
 *   { "by": "project.types", "value": "personal", "position": "first|last" }  -> utility_sort_group
 *   { "by": "date.publish", "order": "asc|desc" }                            -> utility_sort_date
 *   { "by": "seo.title", "order": "asc|desc" }                               -> utility_sort_text
 * A rule can force its type with "type": "date" | "text".
 */
class utility_sort
{
    public static function value(array $params = []): array
    {
        $items = is_array($params['items'] ?? null) ? array_values($params['items']) : [];
        $rules = self::rules($params['rules'] ?? $params['sort'] ?? null);
        if (count($items) < 2 || empty($rules)) {
            return $items;
        }

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
        $va = utility_sort_path::get($a, $rule['by']);
        $vb = utility_sort_path::get($b, $rule['by']);

        return match (self::type($rule)) {
            'group' => utility_sort_group::compare($va, $vb, $rule),
            'date' => utility_sort_date::compare($va, $vb, $rule),
            default => utility_sort_text::compare($va, $vb, $rule),
        };
    }

    private static function type(array $rule): string
    {
        if (array_key_exists('value', $rule)) return 'group';
        $type = strtolower((string) ($rule['type'] ?? ''));
        if ($type !== '') return $type;
        return str_starts_with(ltrim($rule['by'], '@'), 'date.') || str_contains($rule['by'], '.date.') ? 'date' : 'text';
    }

    private static function rules(mixed $rules): array
    {
        if (!is_array($rules)) return [];
        $list = array_is_list($rules) ? $rules : [$rules];

        return array_values(array_filter($list, fn ($rule) =>
            is_array($rule) && is_string($rule['by'] ?? null) && $rule['by'] !== ''
        ));
    }
}
