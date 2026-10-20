<?php

/**
 *   {
 *     "component": "block_workstation_container",
 *     "data": { "id": "w-configuration", "title": "PC Configuration" },
 *     "children": [
 *       { "component": "columns", "data": { "layout": { "base": "100", "lg": "30-70" } }, "children": [
 *           { "component": "column", "children": [
 *               { "component": "block_slider_images", "data": { "img_array_path_dir": "pc-01", "images": ["pc-00.webp"] } },
 *               { "component": "block_workstation_item_listing", "data": { "source": "configuration", "display": "list" } }
 *           ] },
 *           { "component": "block_workstation_item_listing", "data": { "source": "configuration" } }
 *       ] }
 *     ]
 *   }
 *
 * data:
 *   title      string  optional heading
 *   title_tag  string  "h2" (default) | "h3"
 *   class, id  string  optional
 * children: any components (they get post_current_data and the global paths).
 * Colors: --post-color-* of block_workstation_containers. Title: theme .title-divider .title-tertiary .font-primary.
 */
class block_workstation_container
{
    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $children = (string) section::render_children(array_merge(
            array_intersect_key($data, array_flip(self::PASSTHROUGH)),
            ['children' => is_array($data['children'] ?? null) ? $data['children'] : []]
        ));
        if (trim($children) === '') return '';

        $title = self::str($data['title'] ?? '');
        $tag = self::str($data['title_tag'] ?? '') === 'h3' ? 'h3' : 'h2';
        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render([
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'title' => $title !== '' ? '<' . $tag . ' class="title-divider title-tertiary font-primary">' . self::e($title) . '</' . $tag . '>' : '',
            'children' => $children,
        ]);
    }

    private static function str(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
