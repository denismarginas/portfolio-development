<?php

class SectionPostItems
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $posts = $data['posts'] ?? get_data_json('index_data_post_projects', 'index');
        $srcCurrent = __DIR__ . "/../../../../";
        $urlPath = $GLOBALS['urlPath'];

        $searchBar = render_component('post_search_bar', $data);

        $postItems = '';
        foreach ($posts as $post) {
            $postPath = $post["post_id"] . $globalData["page_slug_extension"];

            $postData = flattenPostIndex($post);
            if (isset($postData["display"]) && $postData["display"] == "enable" && isset($postData["exclude_from_search"]) != "true") {
                $postItems .= self::render_post_item($postData, $postPath, $srcCurrent, $urlPath);
            }
        }

        return self::render_template([
            'search_bar' => $searchBar,
            'post_items' => $postItems,
        ]);
    }

    protected static function render_post_item(array $postData, string $postPath, string $srcCurrent, string $urlPath): string
    {
        $shineAnimation = '';
        if (isset($postData["colors"]["post_color_background"])) {
            $shineAnimation = strtoupper($postData["colors"]["post_color_background"]) == "#FFFFFF"
                ? 'data-animation="shine-gray"'
                : 'data-animation="shine"';
        }

        $bgColor = $postData["colors"]["post_color_background"] ?? '';
        $html = '<li class="dm-post-item dm-post-item-media" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.4s">';

        $html .= '<a class="dm-post-item-image" href="' . $postPath . '" ' . $shineAnimation . ' style="background-color: ' . $bgColor . ';">';

        if (isset($postData["logo"]) && isset($postData["logo_path"])) {
            if (isset($postData["logo_type"]) && $postData["logo_type"] == "svg") {
                $html .= svg_get($postData["logo"]);
            } else {
                $html .= renderLogoPost($postData, false, "logo");
            }
        } elseif (isset($postData["thumbnail"]) && isset($postData["thumbnail_path"])) {
            $thumbnail = $urlPath . "src/content/img/" . $postData["post_type"] . "/" . $postData["thumbnail_path"] . "/" . $postData["thumbnail"];
            $html .= render_image($thumbnail, false, "thumbnail");
        }

        $webImagePath = $urlPath . "src/content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/web/home/";
        $mediaImagePath = $urlPath . "src/content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/media/";

        if (file_exists($srcCurrent . $webImagePath)) {
            $getWebImage = getImagesInFolder($srcCurrent . $webImagePath);
            if (!empty($getWebImage) && count($getWebImage) > 0) {
                $html .= render_image($webImagePath . $getWebImage[0], false, 'preview-image');
            }
        } elseif (file_exists($srcCurrent . $mediaImagePath)) {
            $dirs = getDirectoriesInFolder($srcCurrent . $mediaImagePath);
            $dirImagePath = "";
            $getMediaImage = [];

            foreach ($dirs as $dir) {
                $getMediaImage = getImagesInFolder($srcCurrent . $mediaImagePath . $dir . "/");
                if (count($getMediaImage) > 0) {
                    $dirImagePath = $dir . "/";
                    break;
                }
            }

            if (!empty($getMediaImage) && count($getMediaImage) > 0) {
                $html .= render_image($mediaImagePath . $dirImagePath . $getMediaImage[0], false, 'preview-image');
            }
        }

        $html .= '</a>';
        $html .= '<div class="dm-post-item-details">';
        $html .= '<ul class="dm-post-item-categories">';
        foreach ($postData["categories"] as $postCategory) {
            $html .= '<li><span>' . $postCategory . '</span></li>';
        }
        $html .= '</ul>';
        $html .= '<a class="dm-post-item-title" href="' . $postPath . '">' . $postData["title"] . '</a>';
        $html .= '<p class="dm-post-item-description">' . getFirstCharacters($postData["description"], 130) . '</p>';
        $html .= '</div>';
        $html .= '</li>';

        return $html;
    }

    protected static function render_template(array $data): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }
        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }
        return $html;
    }
}
