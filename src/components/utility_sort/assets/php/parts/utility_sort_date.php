<?php

/**
 * Date rule: { "by": "date.publish", "order": "asc|desc" }
 * Accepts "2024-01-31", "Apr 12, 2020", etc. "present" / "in progress" count as today.
 */
class utility_sort_date
{
    private const ONGOING = ['present', 'in progress', 'ongoing', 'now'];

    public static function compare(mixed $a, mixed $b, array $rule): int
    {
        $ta = self::timestamp($a);
        $tb = self::timestamp($b);

        return utility_sort_path::empty_last($ta === null, $tb === null)
            ?? utility_sort_path::direction($rule) * ($ta <=> $tb);
    }

    private static function timestamp(mixed $value): ?int
    {
        if (is_array($value)) $value = reset($value);
        if (!is_scalar($value)) return null;

        $text = strtolower(trim((string) $value));
        if ($text === '') return null;
        if (in_array($text, self::ONGOING, true)) return time();

        $time = strtotime($text);
        return $time === false ? null : $time;
    }
}
