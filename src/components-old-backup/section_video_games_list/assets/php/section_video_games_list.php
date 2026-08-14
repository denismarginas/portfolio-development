<?php

class SectionVideoGamesList
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $videoGames = $data['videoGames'] ?? get_data_json('data_items_games', 'data');
        $urlPath = $GLOBALS['urlPath'];
        $themePath = $urlPath . $globalData["themes_path"] . "/" . $globalData["theme_active"]["dir_name"];

        $scriptsUrl = '';
        $dataUrl = '';
        if (!empty($themePath)) {
            $scriptsUrl = $themePath . '/js/content-data-video-games.js';
            $dataUrl = $urlPath . 'src/content/json/data/data-items-games.json';
        }

        $gameItems = '';
        if (isset($videoGames)) {
            usort($videoGames, fn($a, $b) => strcasecmp($a['name'], $b['name']));

            foreach ($videoGames as $item) {
                $displayAttr = (isset($item["display"]) && $item["display"] == "false") ? ' display="false"' : '';
                $gameItems .= '<li class="dm-vg-item" data-motion="transition-fade-0" data-duration="0.4s" data-delay="0.3s"' . $displayAttr . '>';
                $gameItems .= '<div class="banner-box">';

                if (isset($item["banner"])) {
                    $alt = isset($item["name"]) ? $item["name"] : 'Game Banner';
                    $gameItems .= '<img src="' . $item["banner"] . '" alt="' . $alt . '" class="banner" />';
                }

                $gameItems .= '<div class="details-box">';

                if (isset($item["rank"])) {
                    $gameItems .= '<div class="rank" data-motion="transition-fade-0" data-duration="0.3s" data-delay="0.05s" number="' . round((float)$item['rank']) . '">';
                    $gameItems .= svg_get("star");
                    $gameItems .= '<span>' . $item["rank"] . '</span>';
                    $gameItems .= '</div>';
                }

                if (isset($item["playtime"])) {
                    $gameItems .= '<div class="playtime" data-motion="transition-fade-0" data-duration="0.3s" data-delay="0.05s">';
                    $gameItems .= svg_get("clock");
                    $gameItems .= '<span>' . $item["playtime"] . '</span>';
                    $gameItems .= '</div>';
                }

                if (isset($item["tags"])) {
                    $gameItems .= '<div class="tags-box"><details><summary>' . svg_get("tag-plus") . '</summary>';
                    $gameItems .= '<ul class="tags" data-motion="transition-fade-0" data-duration="0.3s" data-delay="0.05s">';
                    $tags = explode(', ', $item["tags"]);
                    foreach ($tags as $tag) {
                        $gameItems .= '<li>' . htmlspecialchars($tag) . '</li>';
                    }
                    $gameItems .= '</ul></details></div>';
                }

                $gameItems .= '</div></div>';

                if (isset($item["name"])) {
                    $gameItems .= '<p class="name inside" data-motion="transition-fade-0" data-duration="0.3s" data-delay="0.05s">' . $item["name"] . '</p>';
                }

                $gameItems .= '</li>';
            }
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ scripts_url }}', '{{ data_url }}', '{{ game_items }}'],
            [$scriptsUrl, $dataUrl, $gameItems],
            $template
        );
    }
}
