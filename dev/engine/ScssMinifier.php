<?php

class ScssMinifier
{
    public static function minify(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove whitespace around certain characters
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);

        // Remove last semicolons before closing braces
        $css = preg_replace('/;}/', '}', $css);

        // Remove newlines and extra whitespace
        $css = preg_replace('/\s+/', ' ', $css);

        // Remove space before !important
        $css = preg_replace('/\s+!/', '!', $css);

        // Remove leading/trailing whitespace
        $css = trim($css);

        // Simplify colors (#ff0000 → red) - optional micro optimization
        // Shorten hex colors where possible (#ff8844 → #f84)
        $css = preg_replace_callback('/#([0-9a-fA-F])\1([0-9a-fA-F])\2([0-9a-fA-F])\3/', function ($m) {
            return '#' . $m[1] . $m[2] . $m[3];
        }, $css);

        // Remove units from zero values (0px → 0)
        $css = preg_replace('/(\s|:|\()0(?:px|em|rem|pt|cm|mm|in|pc|ex|ch|vw|vh|vmin|vmax|%)(?=[^a-z])/i', '$10', $css);

        return $css;
    }
}
