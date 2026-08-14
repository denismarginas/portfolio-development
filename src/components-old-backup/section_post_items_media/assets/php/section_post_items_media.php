<?php

class SectionPostItemsMedia
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $posts = $data['posts'] ?? get_data_json('index_data_post_projects', 'index');
        $srcCurrent = __DIR__ . "/../../../../";
        $urlPath = $GLOBALS['urlPath'];

        $mediaItems = '';
        foreach ($posts as $post) {
            $postPath = $post["post_id"] . $globalData["page_slug_extension"];

            $postData = flattenPostIndex($post);
            if (isset($postData["display"]) && $postData["display"] == "enable" && isset($postData["exclude_from_search"]) != "true") {
                if (in_array("Visual Media Projects", $postData["categories"]) && !in_array("Miscellaneous Projects", $postData["categories"])) {
                    $mediaItems .= self::render_media_item($postData, $postPath, $srcCurrent, $urlPath);
                }
            }
        }

        return self::render_template([
            'media_items' => $mediaItems,
        ]);
    }

    protected static function render_media_item(array $postData, string $postPath, string $srcCurrent, string $urlPath): string
    {
        $html = '<li class="dm-post-item dm-post-item-media" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.4s">';

        $shineAnimation = '';
        if (isset($postData["colors"]["post_color_background"])) {
            $shineAnimation = strtoupper($postData["colors"]["post_color_background"]) == "#FFFFFF"
                ? 'data-animation="shine-gray"'
                : 'data-animation="shine"';
        }

        $mediaImagePath = $urlPath . "src/content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/media/";
        $mediaTextureImage = $urlPath . "src/content/img/design-elements/overlay-texture-paper.webp";
        $firstImg = null;

        if (file_exists($srcCurrent . $mediaImagePath)) {
            $dirs = getDirectoriesInFolder($srcCurrent . $mediaImagePath);
            $getWebImage = [];
            $dirImagePath = "";

            foreach ($dirs as $dir) {
                $getWebImage = getImagesInFolder($srcCurrent . $mediaImagePath . $dir . "/");
                if (count($getWebImage) > 0) {
                    $dirImagePath = $dir . "/";
                    break;
                }
            }

            if (!empty($getWebImage) && count($getWebImage) > 0) {
                $webImage = $getWebImage[0];
                $firstImg = $mediaImagePath . $dirImagePath . $webImage;
            }
        }

        if ($firstImg) {
            $html .= '<a class="dm-post-view" href="' . $postPath . '#visualmedia">';
            $bgColor = $postData["colors"]["post_color_background"] ?? '';
            $html .= '<div class="photo" style="background-color: ' . $bgColor . ';">';
            $html .= render_image($firstImg);
            $html .= '<div class="logo" style="background-color: ' . $bgColor . ';">';
            $html .= renderLogoPost($postData);
            $html .= '</div>';
            $html .= '<div class="bg-overlay-color" style="background-color: ' . $bgColor . ';"></div>';
            $html .= '</div>';
            if ($mediaTextureImage) {
                $html .= render_image($mediaTextureImage, false, "texture");
            }
            $html .= '</a>';
        } else {
            $bgColor = $postData["colors"]["post_color_background"] ?? '';
            $html .= '<a class="dm-post-item-image" href="' . $postPath . '#visualmedia" ' . $shineAnimation . ' style="background-color: ' . $bgColor . ';">';

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

            if ($firstImg) {
                $html .= render_image($firstImg, false, 'preview-image');
            }

            $html .= '</a>';
        }

        $html .= '<div class="dm-post-item-details">';
        $html .= '<ul class="dm-post-item-categories">';
        foreach ($postData["categories"] as $postCategory) {
            if ($postCategory == "Visual Media Projects") {
                $html .= '<li><span>' . $postCategory . '</span></li>';
            }
        }
        $html .= '</ul>';
        $html .= '<div class="dm-post-item-heading">';
        $html .= '<a class="dm-post-item-title" href="' . $postPath . '#visualmedia">' . $postData["title"] . '</a>';
        if (isset($postData["date"]) && !empty($postData["date"])) {
            $html .= '<p class="dm-post-item-date">' . svg_get('clock') . '<span>' . extract_year_from_date_string($postData["date"]["date_end"]) . '</span></p>';
        }
        $html .= '</div>';
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
