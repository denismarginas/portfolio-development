<?php

require_once __DIR__ . '/parts/hobby_pc_video_games_filters_values.php';
require_once __DIR__ . '/parts/hobby_pc_video_games_filters_render.php';

/**
 * Filter + sort + search bar linked to a hobby_pc_video_games_listing (<ul id="{id}">).
 *
 * $data:
 *   id                  string  id of the listing to control (default "hobby-pc-video-games")
 *   type, render_hidden same as the listing, so the options match its games (hidden games included)
 *   filters             string  item file with the filter definitions (default "filters_pc_video_games")
 *   filters_include     array   only these filter _ids (default: all)
 *   filters_exclude     array   skip these filter _ids
 *   hide_single_option  bool    hide filters with fewer than 2 options (default true)
 *   filters_expanded    bool    filters row open on load (default false)
 *   sort_fields         array   [{ "label": "Name", "by": "name", "type": "text|number", "order": "asc" }, ...]
 *                               (default Name / Rank / Playtime); [] hides the sort toggle. See filters_sort.
 *   sort_default, sort_expanded, sort_by_label, sort_order_label   see filters_sort
 *   search_placeholder, search_button, results_text ("{n}" = count)
 */
class hobby_pc_video_games_filters
{
    public static function render(array $data = []): string
    {
        $target = trim((string) ($data['id'] ?? '')) ?: 'hobby-pc-video-games';

        $renderHidden = ($data['render_hidden'] ?? $data['show_hidden'] ?? true) !== false;
        $items = hobby_pc_video_games_listing_items::find(array_merge($data, ['sort' => [], 'show_hidden' => $renderHidden]));
        if (empty($items)) {
            return '';
        }

        $filters = hobby_pc_video_games_filters_values::filters($data, $items);

        return hobby_pc_video_games_filters_render::form($data, $target, $filters, $items);
    }
}
