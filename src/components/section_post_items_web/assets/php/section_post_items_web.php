<?php

class SectionPostItemsWeb
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $posts = $data['posts'] ?? get_data_json('index_data_post_projects', 'index');
        $deviceLaptopImg = $data['deviceLayoutLaptopImg'] ?? "device-layout-laptop.webp";
        $devicePhoneImg = $data['deviceLayoutPhoneImg'] ?? "device-layout-phone.webp";
        $deviceImgPath = $data['deviceLayoutImgPath'] ?? ($GLOBALS['urlPath'] . "content/img/" . "design-elements" . "/");
        $srcCurrent = __DIR__ . "/../../../../";
        $urlPath = $GLOBALS['urlPath'];

        $webItems = '';
        foreach ($posts as $post) {
            $postPath = $post["post_id"] . $globalData["page_slug_extension"];

            $postData = flattenPostIndex($post);
            if (isset($postData["display"]) && $postData["display"] == "enable" && isset($postData["exclude_from_search"]) != "true") {
                if (in_array("Web Development Projects", $postData["categories"]) && !in_array("Miscellaneous Projects", $postData["categories"])) {
                    $webItems .= self::render_web_item($postData, $postPath, $srcCurrent, $urlPath, $deviceLaptopImg, $devicePhoneImg, $deviceImgPath);
                }
            }
        }

        return self::render_template([
            'web_items' => $webItems,
        ]);
    }

    protected static function render_web_item(array $postData, string $postPath, string $srcCurrent, string $urlPath, string $deviceLaptopImg, string $devicePhoneImg, string $deviceImgPath): string
    {
        $shineAnimation = '';
        if (isset($postData["colors"]["post_color_background"])) {
            $shineAnimation = strtoupper($postData["colors"]["post_color_background"]) == "#FFFFFF"
                ? 'data-animation="shine-gray"'
                : 'data-animation="shine"';
        }

        $haveWebDesktopImage = false;
        $haveWebPhoneImage = false;
        $webImage = '';
        $webImagePath = '';
        $phoneImage = '';

        if (isset($postData["post_type"]) && isset($postData["media_path"])) {
            $webImagePath = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/web/home/";
            if (file_exists($srcCurrent . $webImagePath)) {
                $getWebImage = getImagesInFolder($srcCurrent . $webImagePath);
                if (!empty($getWebImage) && count($getWebImage) > 0) {
                    $webImage = $getWebImage[0];
                    $haveWebDesktopImage = true;
                }
            }
        }

        if (isset($postData["post_type"]) && isset($postData["media_path"])) {
            $webPhoneImagePath = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/web/phone/";
            if (file_exists($srcCurrent . $webPhoneImagePath)) {
                $getWebPhoneImage = getImagesInFolder($srcCurrent . $webPhoneImagePath);
                if (!empty($getWebPhoneImage) && count($getWebPhoneImage) > 0) {
                    $phoneImage = $getWebPhoneImage[0];
                    $haveWebPhoneImage = true;
                }
            }
        }

        $renderBgColor = '';
        if (isset($postData["colors"]) && isset($postData["colors"]["post_color_primary"])) {
            $renderBgColor = 'style="background-color: ' . $postData["colors"]["post_color_primary"] . '"';
        }

        $html = '<li class="dm-post-item dm-post-item-web" data-motion="transition-fade-0" data-duration="0.4s">';

        if ($haveWebDesktopImage) {
            $html .= '<a class="dm-post-view" href="' . $postPath . '#webdevelopment" style="--primary-color-post: ' . $postData["colors"]["post_color_primary"] . ';">';
            $html .= '<div class="device-layout-laptop"><div class="screen" ' . $renderBgColor . '>';
            $html .= render_image($webImagePath . $webImage, false, "web-desktop-image");
            if (!empty($renderBgColor)) {
                $html .= '<div class="shadow-color" ' . $renderBgColor . '></div>';
            }
            $html .= '</div>';
            $html .= render_image($deviceImgPath . $deviceLaptopImg, false, "laptop");
            $html .= '</div>';
            if ($haveWebPhoneImage) {
                $html .= '<div class="device-layout-phone"><div class="screen" ' . $renderBgColor . '>';
                $html .= render_image($webPhoneImagePath . $phoneImage, false, "web-phone-image");
                if (!empty($renderBgColor)) {
                    $html .= '<div class="shadow-color" ' . $renderBgColor . '></div>';
                }
                $html .= '</div>';
                $html .= render_image($deviceImgPath . $devicePhoneImg, false, "phone");
                $html .= '</div>';
            }
            $html .= '</a>';
        } else {
            $bgColor = $postData["colors"]["post_color_background"] ?? '';
            $html .= '<a class="dm-post-item-image" href="' . $postPath . '#webdevelopment" ' . $shineAnimation . ' style="background-color: ' . $bgColor . ';">';
            if (isset($postData["logo"]) && isset($postData["logo_path"])) {
                if (isset($postData["logo_type"]) && $postData["logo_type"] == "svg") {
                    $html .= svg_get($postData["logo"]);
                } else {
                    $html .= renderLogoPost($postData, false, "logo");
                }
            } elseif (isset($postData["thumbnail"]) && isset($postData["thumbnail_path"])) {
                $thumbnail = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["thumbnail_path"] . "/" . $postData["thumbnail"];
                $html .= render_image($thumbnail, false, "thumbnail");
            }

            $webImagePathPreview = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/web/home/";
            if (file_exists($srcCurrent . $webImagePathPreview)) {
                $getWebImage = getImagesInFolder($srcCurrent . $webImagePathPreview);
                if (!empty($getWebImage) && count($getWebImage) > 0) {
                    $html .= render_image($webImagePathPreview . $getWebImage[0], false, 'preview-image');
                }
            }
            $html .= '</a>';
        }

        $html .= '<div class="dm-post-item-details">';
        $html .= '<ul class="dm-post-item-categories">';
        foreach ($postData["categories"] as $postCategory) {
            if ($postCategory == "Web Development Projects") {
                $html .= '<li><span>' . $postCategory . '</span></li>';
            }
        }
        $html .= '</ul>';
        $html .= '<div class="dm-post-item-heading dm-post-web-data">';
        $html .= '<a class="dm-post-item-title" href="' . $postPath . '#webdevelopment">' . $postData["title"] . '</a>';
        if (isset($postData["web_url"]) && !empty($postData["web_url"])) {
            $html .= '<a class="dm-post-item-website" href="' . add_https($postData["web_url"]) . '" target="_blank">' . svg_get('new-tab') . '</a>';
        }
        if (isset($postData["date"]["date_end"]) && !empty($postData["date"]["date_end"])) {
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
