<?php

class utility_sort_by_date
{
    public static function value(array $params = []): array
    {
        $items = $params['items'] ?? [];
        if (!is_array($items) || empty($items)) return [];

        $key = (string) ($params['key'] ?? 'publish');
        $order = strtoupper((string) ($params['order'] ?? 'DESC'));
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $direction = $order === 'ASC' ? 1 : -1;
        usort($items, fn (array $a, array $b): int => $direction * strcmp(
            self::date_of($a, $key),
            self::date_of($b, $key)
        ));

        return $items;
    }

    private static function date_of(array $item, string $key): string
    {
        $date = $item['data']['date'] ?? [];
        if (!is_array($date)) return '';

        return (string) ($date[$key] ?? '');
    }
}