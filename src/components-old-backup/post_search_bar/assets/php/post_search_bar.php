<?php

class PostSearchBar
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');

        $nr_filters = 0;

        $jsonFiltersData = $data['filtersData'] ?? get_data_json('data_post_projects_taxonomies', 'data');
        $nr_filters = count($jsonFiltersData);

        $theme_path = $GLOBALS['urlPath'] . $jsonGlobalData["themes_path"] . "/" . $jsonGlobalData["theme_active"]["dir_name"];

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ theme_path }}', '{{ nr_filters }}', '{{ list_design }}'],
            [$theme_path, $nr_filters, listDesign($nr_filters)],
            $template
        );
    }
}
