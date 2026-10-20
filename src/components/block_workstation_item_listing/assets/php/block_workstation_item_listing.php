<?php

/**
 *   { "component": "block_workstation_item_listing", "data": { "source": "configuration" } }
 *   { "component": "block_workstation_item_listing", "data": { "source": "configuration", "display": "list" } }
 *
 * data:
 *   source   string  item data key with the parts: "configuration" (default) | "accessories" | any other
 *   display  string  "cards" (default): grid of block_workstation_item_card
 *                    "list": the part names as a plain icon_list (chevron-right by default); in list
 *                    mode "svg", "layout" ("inline") and "icon_style" ("plain") go to icon_list
 *   columns  int     cards: columns on desktop (default 3); tablet is always 2, mobile 1 (plain grid, rows fill left to right)
 *   class, id  string  optional
 *
 * Part (data.<source>.<key>): { "name", "tag", "img_src", "description" }; images from
 * <global_img_path>/<data.media.path>/. Card ids: "<source>-<key>".
 */
class block_workstation_item_listing
{
    public static function render(array $data = []): string
    {
        $item = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        $source = preg_replace('/[^a-z0-9_\-]/', '', strtolower(self::str($data['source'] ?? ''))) ?: 'configuration';
        $parts = $item['data'][$source] ?? [];
        if (!is_array($parts) || $parts === []) return '';

        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');

        if (self::str($data['display'] ?? '') === 'list') {
            $names = [];
            foreach ($parts as $part) {
                $name = is_array($part) ? self::str($part['name'] ?? '') : '';
                if ($name !== '') $names[] = $name;
            }
            return (string) PlatformComponentRenderer::render('icon_list', array_merge(
                array_intersect_key($data, array_flip(['svg', 'layout', 'icon_style'])),
                ['items' => $names, 'class' => $class, 'id' => $id]
            ));
        }

        $dir = self::media_dir($item, (string) ($data['global_img_path'] ?? ''));
        $group = 'block-workstation-' . $source . '-' . substr(md5((string) ($item['_id'] ?? '')), 0, 6);

        $cards = [];
        foreach ($parts as $key => $part) {
            if (!is_array($part)) continue;
            $card = (string) PlatformComponentRenderer::render('block_workstation_item_card', [
                'product' => $part,
                'image_dir' => $dir,
                'id' => self::anchor($source, (string) $key),
                'group' => $group,
            ]);
            if (trim($card) !== '') $cards[] = '<li>' . $card . '</li>';
        }
        if ($cards === []) return '';

        $columns = max(1, min(6, (int) ($data['columns'] ?? 3)));

        return PlatformTemplateRenderer::render([
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'style_attr' => $columns !== 3 ? ' style="--block-workstation-item-listing-columns: ' . $columns . '"' : '',
            'items' => implode('', $cards),
        ]);
    }

    private static function anchor(string $source, string $key): string
    {
        return $source . '-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($key)), '-');
    }

    /** "src/content/img/workstation/pc-01/" from global_img_path + data.media.path ('' when missing). */
    private static function media_dir(array $item, string $globalImg): string
    {
        $globalImg = trim(str_replace('\\', '/', $globalImg), '/');
        $media = trim(str_replace('\\', '/', (string) ($item['data']['media']['path'] ?? '')), '/');
        if ($globalImg === '' || str_contains($globalImg . $media, '..')) return '';
        return $globalImg . '/' . ($media !== '' ? $media . '/' : '');
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
