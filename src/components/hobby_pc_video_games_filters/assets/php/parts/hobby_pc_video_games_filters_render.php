<?php

/**
 * HTML of the filter form + the JSON index the JS uses to match listing items
 * (<li data-item-id="…"> inside <ul id="{target}">).
 */
class hobby_pc_video_games_filters_render
{
    private const HTML_DIR = __DIR__ . '/../../html/';

    private const DEFAULT_SORT_FIELDS = [
        ['label' => 'Name', 'by' => 'name', 'type' => 'text', 'order' => 'asc'],
        ['label' => 'Rank', 'by' => 'rank', 'type' => 'number', 'order' => 'desc'],
        ['label' => 'Playtime', 'by' => 'playtime', 'type' => 'number', 'order' => 'desc'],
    ];

    public static function form(array $data, string $target, array $filters, array $items): string
    {
        $texts = PlatformDataService::get_data('content_search_fields') ?? [];
        $expanded = ($data['filters_expanded'] ?? false) === true;

        $filtersHtml = '';
        foreach ($filters as $filter) {
            $filtersHtml .= self::filter($target, $filter);
        }

        $sortData = self::sort_data($data, $target);
        $sortHtml = (string) filters_sort::render($sortData);

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'target' => self::e($target),
            'form_id' => self::e($target . '-filters'),
            'filters_number' => (string) count($filters),
            'filters' => $filtersHtml,
            'filters_hidden' => ($filtersHtml === '' || !$expanded) ? ' hidden' : '',
            'filters_expanded' => $expanded ? 'true' : 'false',
            'filters_button_hidden' => $filtersHtml === '' ? ' hidden' : '',
            'sort' => $sortHtml,
            'sort_hidden' => $sortHtml === '' ? ' hidden' : filters_sort::hidden_attr($sortData),
            'sort_button' => $sortHtml === '' ? '' : filters_sort::toggle_button($sortData),
            'search_placeholder' => self::e((string) ($data['search_placeholder'] ?? $texts['button_placeholder'] ?? 'Search games...')),
            'search_button' => self::e((string) ($data['search_button'] ?? $texts['button_text'] ?? 'Search')),
            'results_text' => self::results_text((string) ($data['results_text'] ?? 'Showing {n} games.'), count(array_filter($items, fn ($i) => !hobby_pc_video_games_listing_items::is_hidden($i)))),
            'icon_filter' => self::icon('filter'),
            'icon_query' => self::icon('filter-query'),
            'index' => self::index($filters, $items),
        ]);
    }

    private static function filter(string $target, array $filter): string
    {
        $placeholder = (string) ($filter['placeholder'] ?? '--select--');
        $style = isset($filter['style']) ? ' style="' . self::e((string) $filter['style']) . '"' : '';
        $prefix = (string) ($filter['option-prefix'] ?? '');
        $suffix = (string) ($filter['option-suffix'] ?? '');

        $isDisplay = hobby_pc_video_games_filters_values::is_display($filter);

        $options = '<option value="">' . self::e($placeholder) . '</option>';
        foreach ($filter['options'] as $option) {
            $value = is_array($option) ? (string) $option['value'] : (string) $option;
            $label = is_array($option) ? (string) $option['label'] : $prefix . $value . $suffix;
            $options .= '<option value="' . self::e($value) . '">' . self::e($label) . '</option>';
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/filter.html', [
            'select_id' => self::e($target . '-' . $filter['_id']),
            'filter_id' => self::e((string) $filter['_id']),
            'name' => self::e((string) ($filter['name'] ?? $filter['_id'])),
            'label' => self::e((string) ($filter['label'] ?? '')),
            'type' => self::e($isDisplay ? 'display' : hobby_pc_video_games_filters_values::type($filter)),
            'mode' => self::e(hobby_pc_video_games_filters_values::mode($filter)),
            'style' => $style,
            'options' => $options,
        ]);
    }

    /** Data for filters_sort (target + sort_fields, default Name / Rank / Playtime). */
    private static function sort_data(array $data, string $target): array
    {
        return array_merge($data, [
            'target' => $target,
            'sort_fields' => $data['sort_fields'] ?? self::DEFAULT_SORT_FIELDS,
        ]);
    }

    /** { "item-id": { "keywords": "...", "game-tags": ["..."], ... } } */
    private static function index(array $filters, array $items): string
    {
        $index = [];
        foreach ($items as $item) {
            $id = hobby_pc_video_games_listing_items::id($item);
            if ($id === '') continue;

            $entry = ['keywords' => self::keywords($item)];
            foreach ($filters as $filter) {
                if (hobby_pc_video_games_filters_values::is_display($filter)) continue;
                $entry[$filter['_id']] = hobby_pc_video_games_filters_values::of($item, $filter);
            }
            $index[$id] = $entry;
        }

        $json = json_encode($index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        return $json === false ? '{}' : $json;
    }

    private static function keywords(array $item): string
    {
        $parts = [
            hobby_pc_video_games_listing_items::id($item),
            (string) ($item['name'] ?? ''),
            implode(' ', hobby_pc_video_games_listing_items::tags($item)),
            (string) ($item['release-date'] ?? ''),
        ];
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', implode(' ', $parts))));
    }

    private static function results_text(string $text, int $count): string
    {
        $parts = explode('{n}', self::e($text), 2);
        $amount = '<span class="query-amount">' . $count . '</span>';
        return count($parts) === 2 ? $parts[0] . $amount . $parts[1] : $parts[0] . ' ' . $amount;
    }

    private static function icon(string $name): string
    {
        return (string) PlatformComponentRenderer::render('svg', ['icon' => $name, 'class' => 'filters-icon']);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
