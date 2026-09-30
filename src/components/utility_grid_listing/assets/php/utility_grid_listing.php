<?php

/**
 * Grid listing attributes for balanced grids.
 *
 *   PlatformComponentRenderer::value('utility_grid_listing', ['count' => 7, 'max' => 4]);
 *   -> ' data-grid-listing="7" data-grid-list-design="items-divisible-by-4" data-grid-listing-max="4"'
 *
 * params:
 *   count  int  number of items (required)
 *   max    int  optional, max columns on big screens (1-9). The design picks the largest
 *               divisor of count that is <= max; when none exists (e.g. 7 items, max 4)
 *               it uses max columns and the last row is simply shorter.
 */
class utility_grid_listing
{
    public static function value(array $params = []): string
    {
        $count = (int) ($params['count'] ?? 0);
        if ($count <= 0) return '';

        $max = (int) ($params['max'] ?? 0);
        if ($max < 1 || $max > 9) {
            return sprintf(
                ' data-grid-listing="%d" data-grid-list-design="items-divisible-by-%d"',
                $count,
                self::divisor_of($count, 9)
            );
        }

        $columns = $count <= $max ? $count : self::divisor_of($count, $max);
        if ($columns < 2 && $count > $max) {
            $columns = $max;
        }

        return sprintf(
            ' data-grid-listing="%d" data-grid-list-design="items-divisible-by-%d" data-grid-listing-max="%d"',
            $count,
            $columns,
            $max
        );
    }

    private static function divisor_of(int $count, int $limit): int
    {
        for ($d = min($limit, $count); $d >= 2; $d--) {
            if ($count % $d === 0) {
                return $d;
            }
        }
        return 1;
    }
}
