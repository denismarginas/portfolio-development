<?php

class utility_grid_listing
{
    public static function value(array $params = []): string
    {
        $count = (int) ($params['count'] ?? 0);
        if ($count <= 0) return '';

        return sprintf(
            ' data-grid-listing="%d" data-grid-list-design="items-divisible-by-%d"',
            $count,
            self::divisor_of($count)
        );
    }

    private static function divisor_of(int $count): int
    {
        for ($d = min(9, $count); $d >= 2; $d--) {
            if ($count % $d === 0) {
                return $d;
            }
        }
        return 1;
    }
}