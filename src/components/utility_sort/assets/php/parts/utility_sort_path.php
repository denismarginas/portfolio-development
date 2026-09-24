<?php

/**
 * Reads a rule's "by" path from an item.
 * "project.types" -> data.project.types, "data.x" / "settings.x" / "@data.x" as written.
 */
class utility_sort_path
{
    public static function get(array $item, string $path): mixed
    {
        $path = ltrim($path, '@');
        if (!str_starts_with($path, 'data.') && !str_starts_with($path, 'settings.')) {
            $path = 'data.' . $path;
        }

        $value = $item;
        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) return null;
            $value = $value[$segment];
        }
        return $value;
    }

    /** Missing / empty values always sort last. Returns null when neither is empty. */
    public static function empty_last(bool $emptyA, bool $emptyB): ?int
    {
        if (!$emptyA && !$emptyB) return null;
        return $emptyA <=> $emptyB;
    }

    public static function direction(array $rule): int
    {
        return strtolower((string) ($rule['order'] ?? 'asc')) === 'desc' ? -1 : 1;
    }
}
