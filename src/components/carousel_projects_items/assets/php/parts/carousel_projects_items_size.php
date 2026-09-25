<?php

/**
 * Inline CSS variables for item size and animation duration.
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

    public static function style(array $data, string $duration): string
    {
        $css = '--carousel-duration: ' . $duration . ';';
        foreach (self::VARS as $key => $var) {
            $value = self::length($data[$key] ?? null);
            if ($value !== '') $css .= ' ' . $var . ': ' . $value . ';';
        }
        return ' style="' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '"';
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
