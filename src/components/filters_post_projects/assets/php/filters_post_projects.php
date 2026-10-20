<?php

require_once __DIR__ . '/parts/filters_post_projects_posts.php';
require_once __DIR__ . '/parts/filters_post_projects_values.php';
require_once __DIR__ . '/parts/filters_post_projects_render.php';

/**
 * Filter + search bar linked to a post-items listing (<ul id="{id}">).
 *
 * $data:
 *   id                  string  id of the post-items listing to control (required)
 *   post_type, filter_by, exclude_by, sort
 *                               same as the post-items block, so the options match its posts
 *   toolbar             string  _id in data_items_listing_toolbar.json with the search / filters / sort / results
 *                               settings (default "projects"); every key below set on the page wins over the toolbar
 *   filters_include     array   only these filter _ids (default: all)
 *   filters_exclude     array   skip these filter _ids
 *   hide_single_option  bool    hide filters with fewer than 2 options (default true)
 *   preview_toggle      bool    show the eye button that toggles preview images (default false)
 *   preview_active      bool    eye button pressed (previews shown) on load (default true); false = starts off
 *   sort_fields         array   sort toggle fields (default Name = seo.title, Publish Date = date.publish); [] hides it
 *   sort_default        string  first Sort by option that keeps the listing's own order (default "Default")
 *   sort_expanded, sort_by_label, sort_order_label   see filters_sort
 *   filters_grid_max    int     max filter columns on big screens, auto-picked from the filter count like
 *                               project_media_gallery (default 4); 0 = old auto-fill columns
 *   filters_column_balancing  bool  balanced rows (utility_grid_column_balancing) instead of filters_grid_max
 *   sort_grid_max, sort_column_balancing   same for the sort row, see filters_sort
 *   filters_expanded    bool    filters row open on load (default false)
 *   search_toggle       bool    search icon button (first, left) that shows the search input to its right (default true);
 *                               false = search input always visible, no button
 *   search_expanded     bool    search input open on load (default true); false = starts closed
 *   search_placeholder, search_button, results_text ("{n}" = count)
 */
class filters_post_projects
{
    public static function render(array $data = []): string
    {
        $data = listing_toolbar::data($data, 'projects');
        $target = trim((string) ($data['id'] ?? ''));
        if ($target === '') {
            return '';
        }

        $posts = filters_post_projects_posts::find($data);
        if (empty($posts)) {
            return '';
        }

        $filters = filters_post_projects_values::filters($data, $posts);

        return filters_post_projects_render::form($data, $target, $filters, $posts);
    }
}
