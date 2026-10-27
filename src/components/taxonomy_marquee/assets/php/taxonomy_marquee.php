<?php

/**
 * Scrolling band of the project's taxonomy.
 *
 *   { "component": "taxonomy_marquee" }
 *
 * data (all optional):
 *   fields     string[]  which values and in which order (default: category, types, tags, platform, collaboration, client):
 *                          category       data.taxonomy.category
 *                          types          data.project.types
 *                          tags           data.taxonomy.tag ("media-web" -> "Media web")
 *                          platform       data.web.platform[].text
 *                          collaboration  data.project.collaboration
 *                          client         data.project.companies
 *   labels     object    label per field, e.g. { "types": "Type" }
 *   items      array     extra { "label", "value" } chips (after the fields); with "fields": [] only these
 *   speed      string    utility_carousel_loop speed (default "normal")
 *   direction  string    "left" (default) | "right"
 *   min_width  int       the chips are repeated until one set is about this wide (default 2560, the band's max
 *                        width), so the loop never shows a gap; the duration grows with it (same speed)
 *   class      string    optional
 */
require_once __DIR__ . '/../../../utility_carousel_loop/assets/php/utility_carousel_loop.php';

class taxonomy_marquee
{
    private const FIELDS = ['category', 'types', 'tags', 'platform', 'collaboration', 'client'];
    private const LABELS = [
        'category' => 'Category',
        'types' => 'Type',
        'tags' => 'Tag',
        'platform' => 'Platform',
        'collaboration' => 'Collaboration',
        'client' => 'Client',
    ];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        $d = is_array($post['data'] ?? null) ? $post['data'] : [];

        $fields = is_array($data['fields'] ?? null) ? $data['fields'] : self::FIELDS;
        $labels = array_merge(self::LABELS, is_array($data['labels'] ?? null) ? array_filter($data['labels'], 'is_string') : []);

        $chips = [];
        foreach ($fields as $field) {
            $field = (string) $field;
            foreach (self::values($field, $d) as $value) $chips[] = [$labels[$field] ?? '', $value];
        }
        foreach ((array) ($data['items'] ?? []) as $item) {
            if (is_array($item)) $chips[] = [self::str($item['label'] ?? ''), self::str($item['value'] ?? '')];
        }

        $items = [];
        $width = 0;
        $seen = [];
        foreach ($chips as [$label, $value]) {
            if ($value === '' || isset($seen[strtolower($label . '|' . $value)])) continue;
            $seen[strtolower($label . '|' . $value)] = true;
            $width += self::width($label, $value);
            $items[] = ($label !== '' ? '<span class="taxonomy-marquee-label text-label">' . self::e($label) . '</span>' : '')
                . '<span class="taxonomy-marquee-value text-label text-label-sm">' . self::e($value) . '</span>';
        }
        if ($items === []) return '';

        // repeat the set until it covers the widest band (the loop shows one set + its copy)
        $repeat = max(1, (int) ceil(max(0, (int) ($data['min_width'] ?? 2560)) / max(1, $width)));
        $set = $items;
        for ($i = 1; $i < $repeat; $i++) $items = array_merge($items, $set);
        $duration = (float) utility_carousel_loop::duration($data['speed'] ?? 'normal') * $repeat;

        $class = self::str($data['class'] ?? '');

        return '<div class="taxonomy-marquee' . ($class !== '' ? ' ' . self::e($class) : '') . '">'
            . PlatformComponentRenderer::render('utility_carousel_loop', [
                'items' => $items,
                // theme utilities: max 2560px wide, centered, faded left / right
                'class' => 'taxonomy-marquee-loop width-limit-2k mask-fade-x',
                'item_class' => 'taxonomy-marquee-item',
                'speed' => rtrim(rtrim(number_format($duration, 2, '.', ''), '0'), '.') . 's',
                'direction' => $data['direction'] ?? 'left',
                'pause_on_hover' => true,
                'gap' => 'var(--dm-spacing-md)',
                'gap_phone' => 'var(--dm-spacing-sm)',
            ])
            . '</div>';
    }

    /** Rough px width of a chip + its dot and gaps (label 10px / value 12px, 2px letter spacing). */
    private static function width(string $label, string $value): int
    {
        return (int) (mb_strlen($label) * 8.5 + mb_strlen($value) * 10 + 12 + 29 + 36);
    }

    /** @return string[] */
    private static function values(string $field, array $d): array
    {
        return match ($field) {
            'category' => self::strings($d['taxonomy']['category'] ?? []),
            'types' => array_map([self::class, 'sentence'], self::strings($d['project']['types'] ?? [])),
            'tags' => array_map(fn ($t) => self::sentence(str_replace(['-', '_'], ' ', $t)), self::strings($d['taxonomy']['tag'] ?? [])),
            'platform' => self::texts($d['web']['platform'] ?? []),
            'collaboration' => self::strings($d['project']['collaboration'] ?? []),
            'client' => self::strings($d['project']['companies'] ?? []),
            default => [],
        };
    }

    /** "online shop" -> "Online shop" */
    private static function sentence(string $text): string
    {
        $text = trim($text);
        return $text === '' ? '' : mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    /** [{ text }] -> texts */
    private static function texts(mixed $items): array
    {
        if (!is_array($items)) return [];
        return array_values(array_filter(array_map(fn ($i) => is_array($i) ? trim((string) ($i['text'] ?? '')) : '', $items), 'strlen'));
    }

    private static function strings(mixed $value): array
    {
        if (is_string($value)) $value = [$value];
        if (!is_array($value)) return [];
        return array_values(array_filter(array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $value), 'strlen'));
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
