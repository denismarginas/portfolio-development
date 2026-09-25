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
 *   filters             string  item file with the filter definitions (default "filters_projects")
 *   filters_include     array   only these filter _ids (default: all)
 *   filters_exclude     array   skip these filter _ids
 *   hide_single_option  bool    hide filters with fewer than 2 options (default true)
 *   preview_toggle      bool    show the eye button that toggles preview images (default false)
 *   filters_expanded    bool    filters row open on load (default false)
 *   search_placeholder, search_button, results_text ("{n}" = count)
 */
class filters_post_projects
{
    public static function render(array $data = []): string
    {
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
