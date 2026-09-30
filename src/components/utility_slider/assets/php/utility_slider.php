<?php

/**
 * Attributes of a slider root element (read by utility_slider.js).
 *
 *   PlatformComponentRenderer::value('utility_slider', ['arrows' => true, 'counter' => true, 'dots' => false])
 *   -> ' data-utility-slider data-slider-arrows="true" data-slider-counter="true" data-slider-dots="false"'
 *
 * Markup the JS expects (the arrows / counter / dots are added by the JS, they are useless without it):
 *
 *   <div class="utility-slider ..." {{ value() }}>
 *     <div class="utility-slider-viewport">
 *       <ul class="utility-slider-track">
 *         <li class="utility-slider-slide">...</li>
 *       </ul>
 *     </div>
 *   </div>
 *
 * Without JS the viewport can still be swiped / scrolled (scroll-snap).
 */
class utility_slider
{
    private const OPTIONS = ['arrows' => true, 'counter' => true, 'dots' => false];

    public static function value(array $params = []): string
    {
        $attrs = ' data-utility-slider';
        foreach (self::OPTIONS as $key => $default) {
            $attrs .= ' data-slider-' . $key . '="' . (self::flag($params, $key, $default) ? 'true' : 'false') . '"';
        }
        return $attrs;
    }

    private static function flag(array $data, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $data)) return $default;
        $v = $data[$key];
        if (is_bool($v)) return $v;
        return !in_array(strtolower(trim((string) $v)), ['false', '0', 'no', 'off', ''], true);
    }
}
