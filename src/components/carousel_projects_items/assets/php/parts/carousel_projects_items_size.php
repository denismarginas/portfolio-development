<?php

/**
 * Inline CSS variables for item size and animation duration.
 * width / height / width_phone / height_phone: "240px", "15rem", a number (= px)
 * or a keyword: "auto", "fit-content", "max-content", "min-content".
 * gap / gap_phone: a length, a number (= px), "--dm-spacing-lg" or "var(--dm-spacing-lg)".
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

    private const GAP_VARS = [
        'gap' => '--carousel-gap',
        'gap_phone' => '--carousel-gap-phone',
    ];

    public static function style(array $data, string $duration): string
    {
        $css = '--carousel-duration: ' . $duration . ';';
        foreach (self::VARS as $key => $var) {
            $value = self::length($data[$key] ?? null);
            if ($value !== '') $css .= ' ' . $var . ': ' . $value . ';';
        }
        foreach (self::GAP_VARS as $key => $var) {
            $value = self::gap($data[$key] ?? null);
            if ($value !== '') $css .= ' ' . $var . ': ' . $value . ';';
        }
        return ' style="' . htmlspecialchars($css, ENT_QUOTES, 'UTF-8') . '"';
    }

    /** "--dm-spacing-lg" -> "var(--dm-spacing-lg)"; "var(--x)" kept; else a length. */
    private static function gap(mixed $value): string
    {
        if (is_string($value)) {
            $value = trim($value);
            if (preg_match('/^--[a-z0-9-]+$/i', $value)) return 'var(' . $value . ')';
            if (preg_match('/^var\(--[a-z0-9-]+\)$/i', $value)) return $value;
        }
        $length = self::length($value);
        return in_array($length, self::KEYWORDS, true) ? '' : $length;
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
