<?php

/**
 * Inline CSS variables for the item size (duration and gaps are set by utility_carousel_loop).
 * width / height / width_phone / height_phone: "240px", "15rem", a number (= px)
 * or a keyword: "auto", "fit-content", "max-content", "min-content".
 */
class carousel_projects_items_size
{
    private const KEYWORDS = ['auto', 'fit-content', 'max-content', 'min-content'];

    private const VARS = [
        'width' => '--carousel-item-width',
        'height' => '--carousel-item-height',
        'width_phone' => '--carousel-item-width-phone',
        'height_phone' => '--carousel-item-height-phone',
    ];

    /** "--carousel-item-width: 240px; ..." for the sizes that are set ('' when none). */
    public static function declarations(array $data): string
    {
        $css = [];
        foreach (self::VARS as $key => $var) {
            $value = self::length($data[$key] ?? null);
            if ($value !== '') $css[] = $var . ': ' . $value . ';';
        }
        return implode(' ', $css);
    }

    private static function length(mixed $value): string
    {
        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            return ((float) $value) . 'px';
        }
        if (!is_string($value)) {
            return '';
        }
        $value = strtolower(trim($value));
        if (in_array($value, self::KEYWORDS, true) || preg_match('/^\d+(\.\d+)?(px|rem|em|vw|vh|%)$/', $value)) {
            return $value;
        }
        return '';
    }
}
