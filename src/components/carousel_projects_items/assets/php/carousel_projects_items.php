<?php

require_once __DIR__ . '/parts/carousel_projects_items_posts.php';
require_once __DIR__ . '/parts/carousel_projects_items_size.php';

/**
 * Infinite-loop carousel of project visuals.
 *
 * $data:
 *   post_type, filter_by, exclude_by, sort   same as post-items
 *   filter_by.max_items int    max items (default 12; "max" at top level still works)
 *   offset             int     skip first N (default 0)
 *   template           object  the item:
 *     component        string  card_post_project_visual_default | _media | _website
 *     params           object  passed to that component
 *     width, height, width_phone, height_phone   item size ("200px", 200 = px)
 *   (visual_component / visual_params / sizes directly in $data still work)
 *   gap, gap_phone     string  space between items, e.g. "--dm-spacing-lg" (default --dm-spacing-sm)
 *   direction          string  "right" | "left" (default "right")
 *   speed              string  "slow" | "normal" | "fast" or a duration like "60s" (default "slow")
 */
class carousel_projects_items
{
    private const DEFAULT_VISUAL = 'card_post_project_visual_default';
    private const VISUALS = [
        'card_post_project_visual_default',
        'card_post_project_visual_media',
        'card_post_project_visual_website',
    ];
    private const SPEEDS = ['slow' => '80s', 'normal' => '50s', 'fast' => '30s'];

    public static function render(array $data = []): string
    {
        $template = is_array($data['template'] ?? null) ? $data['template'] : [];
        $visual = str_replace('-', '_', (string) ($template['component'] ?? $data['visual_component'] ?? self::DEFAULT_VISUAL));
        if (!in_array($visual, self::VISUALS, true)) {
            $visual = self::DEFAULT_VISUAL;
        }
        $visualParams = $template['params'] ?? $data['visual_params'] ?? [];
        $visualParams = is_array($visualParams) ? $visualParams : [];

        $max = post_items_render::max_items($data['filter_by'] ?? null) ?? max(1, (int) ($data['max'] ?? 12));
        $items = '';
        $count = 0;
        foreach (carousel_projects_items_posts::find($data) as $post) {
            if ($count >= $max) break;
            $html = PlatformComponentRenderer::render($visual, array_merge($visualParams, ['post_current_data' => $post]));
            if (trim($html) === '') continue;

            $items .= PlatformTemplateRenderer::render(__DIR__ . '/../html/parts/item.html', [
                'post_id' => htmlspecialchars((string) ($post['_id'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'visual' => $html,
            ]);
            $count++;
        }
        if ($items === '') {
            return '';
        }

        $direction = ($data['direction'] ?? 'right') === 'left' ? 'left' : 'right';
        $speed = (string) ($data['speed'] ?? 'slow');
        $duration = self::SPEEDS[$speed] ?? (preg_match('/^\d+(\.\d+)?m?s$/', $speed) ? $speed : self::SPEEDS['slow']);

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'direction' => $direction,
            'visual' => htmlspecialchars($visual, ENT_QUOTES, 'UTF-8'),
            'style' => carousel_projects_items_size::style(array_merge($data, $template), $duration),
            'items' => $items,
        ]);
    }
}
