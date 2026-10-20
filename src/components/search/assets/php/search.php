<?php

/**
 * Search page: a filters toolbar (same layout as hobby_pc_video_games_filters / filters_post_projects) and the list of
 * every routable page, project, item and taxonomy term (PlatformSearchIndexBuilder::entries()), rendered as cards.
 * The cards start hidden; search.js shows the ones matching the keywords + filters, or all of them with "Show all".
 *
 *   { "component": "search", "data": { "description_max": 100 } }
 *
 * data (all optional):
 *   id                  string  id of the results <ul> (default "search-results")
 *   toolbar             string  _id in data_items_listing_toolbar.json with the search / filters / sort / results settings
 *                               and texts (default "search"); every key below set on the page wins over the toolbar
 *   filters_include, filters_exclude   only / skip these filter _ids
 *   hide_single_option  bool    hide filters with fewer than 2 options (default true)
 *   filters_expanded    bool    filters row open on load (default false)
 *   sort_fields         array   filters_sort fields (default Name A-Z / Z-A, "by": "title"); [] hides the sort toggle
 *   sort_default, sort_expanded, sort_by_label, sort_order_label, sort_grid_max, sort_column_balancing   see filters_sort
 *   filters_column_balancing, filters_grid_max   columns of the filters row, see filters_sort::grid_attrs
 *   description_max     int     card descriptions longer than this are cut to description_max - 3 characters + "..." (default 100)
 *   search_min_chars    int     shortest search that shows results (default 2)
 *
 * Filter definitions (toolbar data.filters.items), one select each:
 *   _id, name, label, placeholder
 *   json-path       "['type']" - entry field: type, type_label or title
 *   options         fixed option values (default: every value found)
 *   option-labels   { "value": "Label" } (default for "type": the singular labels of data_types.json)
 *
 * Texts (toolbar): search.label, search.placeholder, search.button, filters.label, results.label,
 * results.text ({n}), results.hint, results.no_results ({query}), results.show_all.
 */
class search
{
    private const HTML_DIR = __DIR__ . '/../html/';
    private const DEFAULT_SORT_FIELDS = [
        ['label' => 'Name', 'by' => 'title', 'type' => 'text', 'order' => 'asc'],
    ];

    public static function render(array $data = []): string
    {
        $data = listing_toolbar::data($data, 'search');
        $target = trim((string) ($data['id'] ?? '')) ?: 'search-results';
        $text = static fn(string $key, string $default): string => self::e(
            is_scalar($data[$key] ?? null) && trim((string) $data[$key]) !== '' ? (string) $data[$key] : $default
        );

        $entries = self::entries();
        $filters = self::filters($data, $entries);
        $descMax = max(10, (int) ($data['description_max'] ?? 100));

        $filtersHtml = '';
        foreach ($filters as $filter) $filtersHtml .= self::filter($target, $filter);
        $expanded = ($data['filters_expanded'] ?? false) === true;

        $sortData = array_merge($data, [
            'target' => $target,
            'sort_fields' => $data['sort_fields'] ?? self::DEFAULT_SORT_FIELDS,
        ]);
        $sortHtml = (string) filters_sort::render($sortData);

        $items = '';
        $index = [];
        foreach ($entries as $entry) {
            $items .= self::item($entry, $descMax);
            $row = ['keywords' => $entry['keywords'], '_sort' => ['title' => $entry['title']]];
            foreach ($filters as $filter) $row[$filter['_id']] = [(string) ($entry[$filter['field']] ?? '')];
            $index[$entry['id']] = $row;
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'target' => self::e($target),
            'form_id' => self::e($target . '-filters'),
            'input_id' => self::e($target . '-input'),
            'min_chars' => (string) max(1, (int) ($data['search_min_chars'] ?? $data['min_chars'] ?? 2)),
            'hidden' => self::preview_hidden(),
            'filters_number' => (string) count($filters),
            'filters' => $filtersHtml,
            'filters_grid' => filters_sort::grid_attrs(count($filters), $data, 'filters'),
            'filters_hidden' => ($filtersHtml === '' || !$expanded) ? ' hidden' : '',
            'filters_expanded' => $expanded ? 'true' : 'false',
            'filters_button_hidden' => $filtersHtml === '' ? ' hidden' : '',
            'sort' => $sortHtml,
            'sort_hidden' => $sortHtml === '' ? ' hidden' : filters_sort::hidden_attr($sortData),
            'sort_button' => $sortHtml === '' ? '' : filters_sort::toggle_button($sortData),
            'search_label' => $text('search_label', 'Search'),
            'search_placeholder' => $text('search_placeholder', 'Search keywords..'),
            'search_button' => $text('search_button', 'Search'),
            'filters_label' => $text('filters_label', 'Filters'),
            'results_label' => $text('results_label', 'Results'),
            'show_all' => $text('results_show_all', 'Show all'),
            'results_text' => self::results_text($text('results_text', 'Showing {n} results.')),
            'hint' => $text('results_hint', 'Type at least 2 characters to search, or press Show all.'),
            'no_results' => $text('results_no_results', 'No results for "{query}".'),
            'icon_search' => self::icon('search'),
            'icon_filter' => self::icon('filter'),
            'icon_query' => self::icon('filter-query'),
            'icon_all' => self::icon('eye'),
            'index' => self::json($index),
            'items' => $items,
        ]);
    }

    /** Index rows -> entries with id, title, description, url, type, type_label, keywords (lower case, accents removed). */
    private static function entries(): array
    {
        $labels = self::type_labels();
        $out = [];
        foreach (PlatformSearchIndexBuilder::entries() as [$title, $description, $url, $type]) {
            $id = (string) preg_replace('/\.[a-z0-9]+$/i', '', basename((string) $url));
            if ($id === '') continue;
            $typeLabel = $labels[$type] ?? ucwords(str_replace(['_', '-'], ' ', (string) $type));
            $out[] = [
                'id' => $id,
                'title' => (string) $title,
                'description' => (string) $description,
                'url' => PlatformPathService::post_link($id),
                'type' => (string) $type,
                'type_label' => $typeLabel,
                'keywords' => self::fold($title . ' ' . $description),
            ];
        }
        return $out;
    }

    /** Filter definitions with "field" and "options" ([value => label]); single-option filters are left out. */
    private static function filters(array $data, array $entries): array
    {
        $defs = listing_toolbar::filters($data);
        $include = (array) ($data['filters_include'] ?? []);
        $exclude = (array) ($data['filters_exclude'] ?? []);
        $hideSingle = !array_key_exists('hide_single_option', $data) || (bool) $data['hide_single_option'];
        $typeLabels = self::type_labels();

        $filters = [];
        foreach ($defs as $def) {
            if (!is_array($def)) continue;
            $id = (string) ($def['_id'] ?? '');
            if ($id === '' || ($include && !in_array($id, $include, true)) || in_array($id, $exclude, true)) continue;

            $field = preg_match("/^\[\s*['\"]([a-z_]+)['\"]\s*\]$/i", trim((string) ($def['json-path'] ?? '')), $m) ? $m[1] : '';
            if (!in_array($field, ['type', 'type_label', 'title'], true)) continue;

            $found = [];
            foreach ($entries as $entry) {
                $value = (string) ($entry[$field] ?? '');
                if ($value !== '') $found[$value] = true;
            }
            $values = is_array($def['options'] ?? null) && $def['options']
                ? array_values(array_filter(array_map('strval', $def['options']), fn($v) => isset($found[$v])))
                : array_keys($found);
            if (!isset($def['options'])) usort($values, 'strnatcasecmp');
            if (count($values) < ($hideSingle ? 2 : 1)) continue;

            $optionLabels = is_array($def['option-labels'] ?? null) ? $def['option-labels'] : ($field === 'type' ? $typeLabels : []);
            $options = [];
            foreach ($values as $value) $options[$value] = (string) ($optionLabels[$value] ?? $value);

            $def['field'] = $field;
            $def['options'] = $options;
            $filters[] = $def;
        }
        return $filters;
    }

    private static function filter(string $target, array $filter): string
    {
        $options = '<option value="">' . self::e((string) ($filter['placeholder'] ?? '--select--')) . '</option>';
        foreach ($filter['options'] as $value => $label) {
            $options .= '<option value="' . self::e((string) $value) . '">' . self::e($label) . '</option>';
        }
        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/filter.html', [
            'select_id' => self::e($target . '-' . $filter['_id']),
            'filter_id' => self::e((string) $filter['_id']),
            'name' => self::e((string) ($filter['name'] ?? $filter['_id'])),
            'label' => self::e((string) ($filter['label'] ?? '')),
            'options' => $options,
        ]);
    }

    private static function item(array $entry, int $descMax): string
    {
        $desc = $entry['description'];
        if (mb_strlen($desc) > $descMax) $desc = rtrim(mb_substr($desc, 0, $descMax - 3)) . '...';
        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/item.html', [
            'id' => self::e($entry['id']),
            'type' => self::e($entry['type']),
            'url' => self::e($entry['url']),
            'title' => self::e($entry['title']),
            'description' => $desc !== '' ? "\n    " . '<p class="card-text search-card-text">' . self::e($desc) . '</p>' : '',
        ]);
    }

    /** Singular labels of data_types.json (post, item and taxonomy types): "project" -> "Project". */
    private static function type_labels(): array
    {
        $labels = [];
        foreach ((PlatformDataService::get_data('types') ?? []) as $group) {
            foreach ((array) ($group['types'] ?? []) as $key => $type) {
                if (is_array($type) && isset($type['singular'])) $labels[$key] = (string) $type['singular'];
            }
        }
        return $labels;
    }

    /** Preview: keep ?_id=<page> when the form is sent the normal way (without JavaScript). */
    private static function preview_hidden(): string
    {
        if (($GLOBALS['render_target'] ?? '') === 'dist') return '';
        if (!str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/dev/platform/preview/')) return '';
        $id = (string) ($_GET['_id'] ?? '');
        return $id === '' ? '' : '<input type="hidden" name="_id" value="' . self::e($id) . '">';
    }

    /** Lower case without accents ("Ș" -> "s", "ã" -> "a"); search.js folds the typed words the same way. */
    private static function fold(string $text): string
    {
        $text = mb_strtolower(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (class_exists('Normalizer')) {
            $text = (string) preg_replace('/\p{Mn}+/u', '', (string) Normalizer::normalize($text, Normalizer::FORM_D));
        } else {
            $text = strtr($text, [
                'ă' => 'a', 'â' => 'a', 'à' => 'a', 'á' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
                'î' => 'i', 'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
                'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
                'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n',
            ]);
        }
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private static function results_text(string $text): string
    {
        $amount = '<span class="query-amount">0</span>';
        $parts = explode('{n}', $text, 2);
        return count($parts) === 2 ? $parts[0] . $amount . $parts[1] : $text . ' ' . $amount;
    }

    private static function icon(string $name): string
    {
        return (string) PlatformComponentRenderer::render('svg', ['icon' => $name, 'class' => 'filters-icon']);
    }

    private static function json(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        return $json === false ? '{}' : $json;
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
