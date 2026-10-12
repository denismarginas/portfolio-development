<?php

/**
 * Balanced grid attributes for a list (port of the old theme's element-list-design / data-list-design).
 *
 *   PlatformComponentRenderer::value('utility_grid_column_balancing', ['count' => 65]);
 *   -> ' data-grid-listing="65" data-grid-column-balancing="items-divisible-by-13"'
 *
 * params:
 *   count  int  number of items (required)
 *
 * Pattern (the rows of each pattern are always full, see the SCSS):
 *   1 item                          items-exactly-1
 *   divisible by 13, 7, 6, 5, 4, 3 or 2 (first match, largest group first)
 *                                   items-divisible-by-<n>   e.g. 65 -> 13, 5 -> 5, 12 -> 6
 *   none of them (11, 17, 19, 23 ...)
 *                                   items-rest-<count % 4>   4 columns, the last row's items stretch to fill it
 */
class utility_grid_column_balancing
{
    private const GROUPS = [13, 7, 6, 5, 4, 3, 2];

    public static function value(array $params = []): string
    {
        $count = (int) ($params['count'] ?? 0);
        if ($count <= 0) return '';

        return sprintf(' data-grid-listing="%d" data-grid-column-balancing="%s"', $count, self::design($count));
    }

    public static function design(int $count): string
    {
        if ($count === 1) return 'items-exactly-1';

        foreach (self::GROUPS as $group) {
            if ($count % $group === 0) return 'items-divisible-by-' . $group;
        }

        return 'items-rest-' . ($count % 4);
    }
}
