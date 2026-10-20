<?php

/**
 *   { "component": "hobby_listing", "data": { "title": "List of hobbies" } }
 *
 * data:
 *   title      string | object  heading, optional: "List of hobbies"
 *                       or { "text": "List of hobbies", "tag": "h2", "class": "title-divider title-primary" }
 *   type       string  item file (default "hobby" -> data_items_hobby.json)
 *   ids        array   optional: only these item _ids, in this order
 *   columns    int     max columns on big screens (default 4)
 *   variant    string  card variant: "stack" (default) | "row"
 *   class, id  string  optional
 *
 * Hobby item:
 *   { "_id": "video-games",
 *     "settings": { "render": true },
 *     "data": { "title": "Video Games", "feature_image": "src/content/img/hobby/hobby-video-games.webp",
 *               "svg": "hobby-video-games", "link": { "_id": "hobby-pc-video-games" } } }
 *   data.link (optional, the card becomes a link):
 *     { "_id": "workstations" }                internal page / project / category
 *     { "url": "https://...", "target": "_blank" }  external (target defaults to "_blank")
 *     optional "params": { "project_types": "media-video" } (-> ?project_types=media-video) and "anchor": "section-id"
 *   settings.render = false hides the card.
 */
class hobby_listing
{
    public static function render(array $data = []): string
    {
        $type = self::str($data['type'] ?? '') ?: 'hobby';
        $items = PlatformDataService::get_all_items_from_file($type);
        if (!is_array($items) || empty($items)) return '';

        $items = array_values(array_filter($items, fn ($item) => is_array($item) && ($item['settings']['render'] ?? true) !== false));

        $ids = is_array($data['ids'] ?? null) ? array_values(array_filter(array_map([self::class, 'str'], $data['ids']))) : [];
        if (!empty($ids)) {
            $byId = [];
            foreach ($items as $item) $byId[self::str($item['_id'] ?? '')] = $item;
            $items = array_values(array_filter(array_map(fn ($id) => $byId[$id] ?? null, $ids)));
        }

        $cards = [];
        foreach ($items as $item) {
            $fields = is_array($item['data'] ?? null) ? $item['data'] : $item;
            $card = [
                'text' => self::str($fields['title'] ?? $fields['name'] ?? ''),
                'svg' => self::str($fields['svg'] ?? ''),
                'img' => self::str($fields['feature_image'] ?? $fields['img'] ?? ''),
            ];
            if (isset($fields['link'])) $card['link'] = $fields['link'];
            if ($card['text'] === '' && $card['svg'] === '') continue;
            $cards[] = $card;
        }
        if (empty($cards)) return '';

        $columns = (int) ($data['columns'] ?? 4);
        $cardsHtml = (string) PlatformComponentRenderer::render('card_mini_icon_and_text', [
            'variant' => self::str($data['variant'] ?? '') ?: 'stack',
            'columns' => $columns > 0 ? $columns : 4,
            'list_class' => 'hobby-listing-items',
            'items' => $cards,
        ]);
        if (trim($cardsHtml) === '') return '';

        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'title' => (string) PlatformComponentRenderer::render('title', title::normalize($data['title'] ?? null, $data)),
            'cards' => $cardsHtml,
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
