<?php

class SectionPostItemsMiscellaneous
{
    public static function render(array $data = []): string
    {
        $personalData = get_data_json('data_content_personal', 'data');
        $deviceData = $personalData["post_projects"]["img"] ?? [];
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $posts = $data['posts'] ?? get_data_json('index_data_post_projects', 'index');
        $deviceLaptopImg = $data['deviceLayoutLaptopImg'] ?? ($deviceData["devices"]["post_laptop"] ?? "");
        $devicePhoneImg = $data['deviceLayoutPhoneImg'] ?? ($deviceData["devices"]["post_phone"] ?? "");
        $deviceImgPath = $data['deviceLayoutImgPath'] ?? $GLOBALS['urlPath'];
        $srcCurrent = __DIR__ . "/../../../../";
        $urlPath = $GLOBALS['urlPath'];

        $miscItems = '';
        foreach ($posts as $post) {
            $postPath = $post["post_id"] . $globalData["page_slug_extension"];

            $postData = flattenPostIndex($post);
            if (isset($postData["display"]) && $postData["display"] == "enable" && isset($postData["exclude_from_search"]) != "true") {
                if (in_array("Miscellaneous Projects", $postData["categories"])) {
                    $miscItems .= self::render_misc_item($postData, $postPath, $srcCurrent, $urlPath, $deviceLaptopImg, $devicePhoneImg, $deviceImgPath);
                }
            }
        }

        return self::render_template([
            'misc_items' => $miscItems,
        ]);
    }

    protected static function render_misc_item(array $postData, string $postPath, string $srcCurrent, string $urlPath, string $deviceLaptopImg, string $devicePhoneImg, string $deviceImgPath): string
    {
        $haveWebDesktopImage = false;
        $haveWebPhoneImage = false;
        $webImage = '';
        $webImagePath = '';
        $phoneImage = '';
        $webPhoneImagePath = '';
        $firstImg = null;
        $renderBgColor = '';
        $addClass = '';

        if (in_array("Web Development Projects", $postData["categories"])) {
            if (isset($postData["post_type"]) && isset($postData["media_path"])) {
                $webImagePath = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/web/home/";
                if (file_exists($srcCurrent . $webImagePath)) {
                    $getWebImage = getImagesInFolder($srcCurrent . $webImagePath);
                    if (!empty($getWebImage) && count($getWebImage) > 0) {
                        $webImage = $getWebImage[0];
                        $haveWebDesktopImage = true;
                    }
                }

                $webPhoneImagePath = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/web/phone/";
                if (file_exists($srcCurrent . $webPhoneImagePath)) {
                    $getWebPhoneImage = getImagesInFolder($srcCurrent . $webPhoneImagePath);
                    if (!empty($getWebPhoneImage) && count($getWebPhoneImage) > 0) {
                        $phoneImage = $getWebPhoneImage[0];
                        $haveWebPhoneImage = true;
                    }
                }
            }

            if (isset($postData["colors"]) && isset($postData["colors"]["post_color_primary"])) {
                $renderBgColor = 'style="background-color: ' . $postData["colors"]["post_color_primary"] . '"';
            }
            $addClass = "dm-post-item-web";
        } elseif (in_array("Visual Media Projects", $postData["categories"])) {
            $mediaImagePath = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["media_path"] . "/media/";
            if (file_exists($srcCurrent . $mediaImagePath)) {
                $dirs = getDirectoriesInFolder($srcCurrent . $mediaImagePath);
                $dirImagePath = "";
                $getWebImage = [];

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
            $addClass = "dm-post-item-media";
        } elseif (in_array("Miscellaneous Projects", $postData["categories"])) {
            $addClass = "";
        }

        $shineAnimation = '';
        if (isset($postData["colors"]["post_color_background"])) {
            $shineAnimation = strtoupper($postData["colors"]["post_color_background"]) == "#FFFFFF"
                ? 'data-animation="shine-gray"'
                : 'data-animation="shine"';
        }

        $html = '<li class="dm-post-item ' . $addClass . '" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.4s">';

        if (in_array("Web Development Projects", $postData["categories"]) && in_array("Miscellaneous Projects", $postData["categories"]) && $haveWebDesktopImage) {
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
        } elseif (in_array("Visual Media Projects", $postData["categories"]) && in_array("Miscellaneous Projects", $postData["categories"]) && $firstImg) {
            $bgColor = $postData["colors"]["post_color_background"] ?? '';
            $html .= '<a class="dm-post-view" href="' . $postPath . '#visualmedia">';
            $html .= '<div class="photo" style="background-color: ' . $bgColor . ';">';
            $html .= render_image($firstImg);
            if (isset($postData["logo"]) && isset($postData["logo_path"])) {
                $html .= '<div class="logo" style="background-color: ' . $bgColor . ';">' . renderLogoPost($postData) . '</div>';
            }
            $html .= '<div class="bg-overlay-color" style="background-color: ' . $bgColor . ';"></div>';
            $html .= '</div>';
            $html .= render_image($urlPath . "content/img/design-elements/overlay-texture-paper.webp", false, "texture");
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
                $thumbnail = $urlPath . "content/img/" . $postData["post_type"] . "/" . $postData["thumbnail_path"] . "/" . $postData["thumbnail"];
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
            if ($postCategory == "Web Development Projects") {
                $html .= '<li><span>' . $postCategory . '</span></li>';
            }
        }
        $html .= '</ul>';
        $html .= '<div class="dm-post-item-heading">';
        $html .= '<a class="dm-post-item-title" href="' . $postPath . '#webdevelopment">' . $postData["title"] . '</a>';
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
