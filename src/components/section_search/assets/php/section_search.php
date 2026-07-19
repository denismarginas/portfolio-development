<?php

class SectionSearch
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $indexPages = $data['indexPages'] ?? get_data_json('index_data_post_pages', 'index');
        $urlPath = $GLOBALS['urlPath'];
        $themePath = $urlPath . $globalData["themes_path"] . "/" . $globalData["theme_active"]["dir_name"];

        $indexDataUrl = $urlPath . 'content/json/index/index-data-post-pages.json';
        $searchJsUrl = $themePath . '/js/content-data-search.js';
        $searchTitle = $globalData["search_fields"]["title"];
        $searchPlaceholder = $globalData["search_fields"]["button_placeholder"];
        $searchButtonText = $globalData["search_fields"]["button_text"];

        $closeSvg = svg_get('close');

        $searchItems = '';
        foreach ($indexPages as $item) {
            $pageSlug = pathinfo($item["page"], PATHINFO_FILENAME) . $globalData["page_slug_extension"];
            $searchItems .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.4s" class="search-item">';
            $searchItems .= '<a class="search-item-image" href="' . $pageSlug . '">';
            $searchItems .= '<img src="' . $urlPath . 'content/img/placeholder/page-placeholder.svg" lazy-load="true">';
            $searchItems .= '<div class="preview-image"><img src="' . $item["default_img"] . '" lazy-load="true"></div>';
            $searchItems .= '</a>';
            $searchItems .= '<div class="search-item-data">';
            $searchItems .= '<a class="title" href="' . $pageSlug . '">' . $item["meta_title"] . '</a>';
            $searchItems .= '<div class="list"><span class="post_type">' . $item["post_type"] . '</span></div>';
            $searchItems .= '<p class="description">' . $item["meta_description"] . '</p>';
            $searchItems .= '</div>';
            $searchItems .= '</li>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ index_data_url }}', '{{ search_js_url }}', '{{ search_title }}', '{{ search_placeholder }}', '{{ search_button_text }}', '{{ close_svg }}', '{{ search_items }}'],
            [$indexDataUrl, $searchJsUrl, $searchTitle, $searchPlaceholder, $searchButtonText, $closeSvg, $searchItems],
            $template
        );
    }
}
