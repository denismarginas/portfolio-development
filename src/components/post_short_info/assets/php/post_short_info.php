<?php

/**
 * Post summary: visual + content (+ optional children), rendered one after
 * another with no wrapper element. Spacing / layout comes from the parent
 * (section_layout_aside's <aside> is a flex column).
 *
 *   { "component": "post_short_info" }
 *
 * data:
 *   post_id        string          optional, show another post (default: current post)
 *   show_visual    bool            default true
 *   show_content   bool            default true
 *   visual         object          extra data for post_short_info_visual (e.g. {"show_preview": false})
 *   content        object          extra data for post_short_info_content (e.g. {"fields": ["title","date"]})
 *
 * children: any components, rendered after the content card.
 */
class post_short_info
{
    private const PASSTHROUGH = ['post_current_data', 'post_id', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $visual = '';
        if (post_short_info_post::flag($data, 'show_visual', true)) {
            $visual = PlatformComponentRenderer::render('post_short_info_visual', array_merge(self::sub($data, 'visual'), $shared));
        }

        $content = '';
        if (post_short_info_post::flag($data, 'show_content', true)) {
            $content = PlatformComponentRenderer::render('post_short_info_content', array_merge(self::sub($data, 'content'), $shared));
        }

        $children = section::render_children(array_merge($shared, ['children' => $data['children'] ?? []]));

        if (trim($visual . $content . $children) === '') return '';

        return PlatformTemplateRenderer::render([
            'visual' => $visual,
            'content' => $content,
            'children' => $children,
        ]);
    }

    private static function sub(array $data, string $key): array
    {
        $value = $data[$key] ?? [];
        if (is_string($value)) $value = json_decode($value, true); // editor stores objects as JSON text
        return is_array($value) ? $value : [];
    }
}
