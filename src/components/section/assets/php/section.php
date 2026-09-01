<?php

class section
{
    /**
     * Generic <section><container> wrapper. Any component block placed in
     * this section's "children" (the same {component, data, children} shape
     * used everywhere else - see platform_render_body_sections()) gets
     * rendered and dropped straight into the container, in order.
     */
    public static function render(array $data = []): string
    {
        $class = trim((string) ($data['class'] ?? ''));
        $extraClass = $class !== '' ? ' ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') : '';

        $id = trim((string) ($data['id'] ?? ''));
        $idAttr = $id !== '' ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : '';

        return PlatformTemplateRenderer::render([
            'extra_class' => $extraClass,
            'id_attr' => $idAttr,
            'children' => self::render_children($data),
        ]);
    }

    private static function render_children(array $data): string
    {
        $children = $data['children'] ?? [];

        // Already-rendered HTML passed straight in (same fallback chain as
        // page_content.php) - lets this component also be used as a plain
        // wrapper around markup instead of a list of component blocks.
        if (is_string($children)) return $children;

        if (!is_array($children) || empty($children)) {
            return (string) ($data['content'] ?? $data['body_content'] ?? $data['children_html'] ?? '');
        }

        $passthrough = array_intersect_key($data, array_flip([
            'post_current_data',
            'global_content_path',
            'global_img_path',
            'global_vid_path',
        ]));

        $html = '';
        foreach ($children as $item) {
            $componentName = (string) ($item['component'] ?? '');
            if ($componentName === '') continue;

            $componentData = array_merge($item['data'] ?? [], $passthrough, [
                'children' => $item['children'] ?? $item['data']['children'] ?? [],
            ]);

            $html .= PlatformComponentRenderer::render(str_replace('-', '_', $componentName), $componentData);
        }

        return $html;
    }
}
