<?php

/**
 * Renders another post's "content" blocks in place.
 *
 *   { "component": "utility_duplicate_content", "data": { "post_id": "home" } }
 *
 * data:
 *   post_id    string  _id of the post whose content is rendered (required)
 *   post_type  string  post file name: "page" -> data_post_page.json (default "page")
 *
 * The blocks get the CURRENT page as post_current_data, so "@data.seo.title" style
 * references (e.g. in a hero) still read this page's own SEO data.
 * Self references and loops (A -> B -> A) are skipped.
 */
class utility_duplicate_content
{
    private const PASSTHROUGH = ['global_content_path', 'global_img_path', 'global_vid_path'];

    /** post keys currently being rendered (loop guard) */
    private static array $stack = [];

    public static function render(array $data = []): string
    {
        $postId = self::str($data['post_id'] ?? $data['_id'] ?? '');
        if ($postId === '') return '';
        $postType = self::str($data['post_type'] ?? '') ?: 'page';

        $current = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        $currentId = self::str($current['_id'] ?? '');

        $key = $postType . ':' . $postId;
        if ($postId === $currentId || isset(self::$stack[$key])) return '';

        $post = PlatformDataService::get_post_by_id($postType, $postId);
        $items = $post['data']['content'] ?? $post['content'] ?? null;
        if (!is_array($items) || empty($items)) return '';

        $base = array_intersect_key($data, array_flip(self::PASSTHROUGH));
        $base['post_current_data'] = $current ?: $post;

        self::$stack[$key] = true;
        $html = '';
        try {
            foreach ($items as $item) {
                if (!is_array($item)) continue;
                $name = str_replace('-', '_', self::str($item['component'] ?? ''));
                if ($name === '') continue;

                $html .= PlatformComponentRenderer::render($name, array_merge(
                    is_array($item['data'] ?? null) ? $item['data'] : [],
                    $base,
                    ['children' => $item['children'] ?? $item['data']['children'] ?? []]
                ));
            }
        } finally {
            unset(self::$stack[$key]);
        }

        return $html;
    }

    private static function str(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
