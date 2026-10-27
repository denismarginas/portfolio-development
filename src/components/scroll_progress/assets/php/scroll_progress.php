<?php

/**
 * Reading progress bar (scroll_progress.js fills it with transform: scaleX, no layout work).
 *
 *   { "component": "scroll_progress", "data": { "position": "fixed" } }
 *
 * data:
 *   position  string  "bottom" (default) | "top" - on that edge of the parent element (position: absolute,
 *                     the parent needs position: relative / sticky), or "fixed" - top of the screen
 *   target    string  what is measured: "page" (default, the whole page) or a selector / #id of one element
 *                     (0% when its top reaches the top of the screen, 100% when its bottom does)
 *   class     string  optional extra class
 *
 * Color: --scroll-progress-color on a parent, else --post-color-primary, else --dm-color-primary.
 */
class scroll_progress
{
    private const POSITIONS = ['bottom', 'top', 'fixed'];

    public static function render(array $data = []): string
    {
        $position = strtolower(trim((string) ($data['position'] ?? 'bottom')));
        if (!in_array($position, self::POSITIONS, true)) $position = 'bottom';

        $target = trim((string) ($data['target'] ?? 'page'));
        $class = trim((string) ($data['class'] ?? ''));

        return PlatformTemplateRenderer::render([
            'position' => $position,
            'extra_class' => $class !== '' ? ' ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') : '',
            'target_attr' => $target !== '' && $target !== 'page'
                ? ' data-scroll-progress-target="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"'
                : '',
        ]);
    }
}
