<?php

/**
 * HTML of the filter form + the JSON index the JS uses to match listing items
 * (<li data-post-id="…"> inside <ul id="{target}">).
 */
class filters_post_projects_render
{
    private const HTML_DIR = __DIR__ . '/../../html/';

    private const DEFAULT_SORT_FIELDS = [
        ['label' => 'Name', 'by' => 'seo.title', 'type' => 'text', 'order' => 'asc'],
        ['label' => 'Publish Date', 'by' => 'date.publish', 'type' => 'date', 'order' => 'desc'],
    ];

    public static function form(array $data, string $target, array $filters, array $posts): string
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
            'search_placeholder' => self::e((string) ($data['search_placeholder'] ?? $texts['button_placeholder'] ?? 'Search keywords...')),
            'search_button' => self::e((string) ($data['search_button'] ?? $texts['button_text'] ?? 'Search')),
            'results_text' => self::results_text((string) ($data['results_text'] ?? 'Showing {n} results.'), count($posts)),
            'icon_filter' => self::icon('filter'),
            'icon_query' => self::icon('filter-query'),
            'preview_button' => self::preview_button($data),
            'sort' => $sortHtml,
            'sort_hidden' => $sortHtml === '' ? ' hidden' : filters_sort::hidden_attr($sortData),
            'sort_button' => $sortHtml === '' ? '' : filters_sort::toggle_button($sortData),
            'index' => self::index($filters, $posts, $sortHtml === '' ? null : $sortData),
        ]);
    }

    private static function filter(string $target, array $filter): string
    {
        $placeholder = (string) ($filter['placeholder'] ?? '--select--');
        $style = isset($filter['style']) ? ' style="' . self::e((string) $filter['style']) . '"' : '';

        $options = '<option value="">' . self::e($placeholder) . '</option>';
        foreach ($filter['options'] as $value) {
            $options .= '<option value="' . self::e($value) . '"' . $style . '>' . self::e($value) . '</option>';
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/filter.html', [
            'select_id' => self::e($target . '-' . $filter['_id']),
            'filter_id' => self::e((string) $filter['_id']),
            'name' => self::e((string) ($filter['name'] ?? $filter['_id'])),
            'label' => self::e((string) ($filter['label'] ?? '')),
            'type' => self::e((string) ($filter['json-type'] ?? 'string')),
            'mode' => self::e((string) ($filter['json-date-search-mode'] ?? '')),
            'style' => $style,
            'options' => $options,
        ]);
    }

    /** Data for filters_sort (target + sort_fields + "Default" option for the listing's own order). */
    private static function sort_data(array $data, string $target): array
    {
        return array_merge($data, [
            'target' => $target,
            'sort_fields' => $data['sort_fields'] ?? self::DEFAULT_SORT_FIELDS,
            'sort_default' => $data['sort_default'] ?? 'Default',
        ]);
    }

    /** { "post-id": { "keywords": "...", "post-category": ["..."], "_sort": { "seo.title": "...", ... } } } */
    private static function index(array $filters, array $posts, ?array $sortData = null): string
    {
        $index = [];
        foreach ($posts as $post) {
            $id = filters_post_projects_posts::id($post);
            if ($id === '') continue;

            $entry = ['keywords' => self::keywords($post)];
            foreach ($filters as $filter) {
                $entry[$filter['_id']] = filters_post_projects_values::of($post, $filter);
            }
            if ($sortData !== null) {
                $entry['_sort'] = filters_sort::values($post, $sortData);
            }
            $index[$id] = $entry;
        }

        $json = json_encode($index, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        return $json === false ? '{}' : $json;
    }

    private static function keywords(array $post): string
    {
        $seo = $post['data']['seo'] ?? [];
        $parts = [filters_post_projects_posts::id($post), $seo['title'] ?? '', $seo['description'] ?? '', $seo['keywords'] ?? ''];
        $data = is_array($post['data'] ?? null) ? $post['data'] : [];
        array_walk_recursive($data, function ($v, $k) use (&$parts) {
            if (is_string($v) && in_array($k, ['text', 'employer'], true)) $parts[] = $v;
        });
        foreach (['types', 'companies', 'contractors', 'collaboration'] as $key) {
            foreach ((array) ($data['project'][$key] ?? []) as $v) {
                if (is_string($v)) $parts[] = $v;
            }
        }
        foreach ((array) ($data['taxonomy'] ?? []) as $terms) {
            foreach ((array) $terms as $v) {
                if (is_string($v)) $parts[] = $v;
            }
        }
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($parts, 'is_string')))));
    }

    /** Eye button that toggles .show-preview on the listing; only with "preview_toggle": true. */
    private static function preview_button(array $data): string
    {
        if (($data['preview_toggle'] ?? false) !== true) {
            return '';
        }
        return '<button type="button" class="icon toggle-preview" aria-pressed="false" aria-label="Preview images">' . self::icon('eye') . '</button>';
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
