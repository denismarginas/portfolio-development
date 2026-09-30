<?php

/**
 * settings.appearance.colors -> --post-color-* CSS variables.
 *
 *   PlatformComponentRenderer::value('utility_appearance_colors', [
 *       'post_current_data' => $post,   // or 'colors' => ['primary' => '#ffcc00', ...]
 *       'output' => 'attribute',        // "attribute" (default) | "declarations" | "style_tag"
 *       'selector' => ':root',          // only for "style_tag"
 *   ]);
 *
 *   attribute     ->  style="--post-color-primary: #ffcc00; ..."   (leading space included)
 *   declarations  ->  --post-color-primary: #ffcc00; ...           (to merge into another style attr)
 *   style_tag     ->  <style>:root{--post-color-primary: #ffcc00; ...}</style>
 *
 * Missing colors use the fallbacks below (same values as theme interface/colors-post.scss).
 * Extra keys in appearance.colors are output too (without a fallback).
 */
class utility_appearance_colors
{
    public const FALLBACKS = [
        'primary' => 'var(--dm-color-primary-600)',
        'secondary' => 'var(--dm-color-primary-50)',
        'canvas_primary' => 'var(--dm-color-primary-800)',
        'canvas_secondary' => 'var(--dm-color-primary-50)',
    ];

    public static function value(array $params = []): string
    {
        $colors = $params['colors'] ?? ($params['post_current_data']['settings']['appearance']['colors'] ?? []);
        $colors = is_array($colors) ? $colors : [];

        $declarations = self::declarations($colors);
        $output = strtolower(trim((string) ($params['output'] ?? 'attribute')));

        if ($output === 'declarations') {
            return $declarations;
        }

        if ($output === 'style_tag') {
            $selector = trim((string) ($params['selector'] ?? ':root'));
            if ($selector === '' || preg_match('/[{}<>]/', $selector)) $selector = ':root';
            return '<style>' . $selector . '{' . $declarations . '}</style>';
        }

        return ' style="' . htmlspecialchars($declarations, ENT_QUOTES, 'UTF-8') . '"';
    }

    public static function declarations(array $colors): string
    {
        $values = [];
        foreach (self::FALLBACKS as $key => $fallback) {
            $values[$key] = self::clean($colors[$key] ?? '') ?: $fallback;
        }
        foreach ($colors as $key => $value) {
            $key = (string) $key;
            if (!isset($values[$key]) && ($clean = self::clean($value)) !== '') {
                $values[$key] = $clean;
            }
        }

        $out = [];
        foreach ($values as $key => $value) {
            $name = preg_replace('/[^a-z0-9\-]/', '', str_replace('_', '-', strtolower($key)));
            if ($name !== '') $out[] = '--post-color-' . $name . ': ' . $value . ';';
        }
        return implode(' ', $out);
    }

    /** Only plain color values (no ; { } < > " ' characters). */
    private static function clean(mixed $value): string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';
        return ($value === '' || preg_match('/[;{}<>"\']/', $value)) ? '' : $value;
    }
}
