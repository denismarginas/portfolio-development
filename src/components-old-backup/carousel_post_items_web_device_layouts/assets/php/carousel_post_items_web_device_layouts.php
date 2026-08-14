<?php

class CarouselPostItemsWebDeviceLayouts
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $posts = $data['posts'] ?? get_data_json('index_data_post_projects', 'index');
        $carousel_direction = $data['carouselDirection'] ?? "right";
        $carousel_speed = $data['carouselSpeed'] ?? "slow";
        $offset_items = $data['offsetItems'] ?? 0;
        $max_items = $data['maxItems'] ?? 8;

        usort($posts, "dateStartPostSortDesc");
        usort($posts, "personalTypePostProjectSortAsc");

        $personal_data = get_data_json('data_content_personal', 'data')["post_projects"]["img"] ?? [];
        $device_layout_laptop_img = $personal_data["devices"]["post_laptop"] ?? "";
        $device_layout_phone_img = $personal_data["devices"]["post_phone"] ?? "";
        $device_layout_img_path = $GLOBALS['urlPath'];

        if (count($posts) < 1 || empty($posts)) {
            return '';
        }

        $carouselItems = '';
        $nr_item = 1;
        foreach ($posts as $post) {
            $have_web_desktop_image = false;
            $have_web_phone_image = false;
            $post_path = $post["post_id"] . $jsonGlobalData["page_slug_extension"];

            $post_data = flattenPostIndex($post);

            if (isset($post_data["post_type"]) && isset($post_data["media_path"])) {
                $web_image_path = $GLOBALS['urlPath'] . "src/content/img/" . $post_data["post_type"] . "/" . $post_data["media_path"] . "/web/home/";
                $abs_path = realpath(__DIR__ . "/../../../../../") . "/" . $web_image_path;
                if (is_dir($abs_path)) {
                    $get_web_image = getImagesInFolder($abs_path);
                    if (!empty($get_web_image)) {
                        $web_image = $get_web_image[0];
                        $have_web_desktop_image = true;
                    }
                }
            }

            if (isset($post_data["post_type"]) && isset($post_data["media_path"])) {
                $web_phone_image_path = $GLOBALS['urlPath'] . "src/content/img/" . $post_data["post_type"] . "/" . $post_data["media_path"] . "/web/phone/";
                $abs_path = realpath(__DIR__ . "/../../../../../") . "/" . $web_phone_image_path;
                if (is_dir($abs_path)) {
                    $get_web_phone_image = getImagesInFolder($abs_path);
                    if (!empty($get_web_phone_image)) {
                        $phone_image = $get_web_phone_image[0];
                        $have_web_phone_image = true;
                    }
                }
            }

            $render_bg_color = '';
            if (isset($post_data["colors"]) && isset($post_data["colors"]["post_color_primary"])) {
                $render_bg_color = 'style="background-color: ' . htmlspecialchars($post_data["colors"]["post_color_primary"]) . '"';
            }

            if (
                isset($post_data["categories"]) &&
                in_array("Web Development Projects", $post_data["categories"]) &&
                !in_array("Visual Media Projects", $post_data["categories"]) &&
                !in_array("Miscellaneous Projects", $post_data["categories"]) &&
                $have_web_desktop_image != false &&
                $max_items > $nr_item &&
                $nr_item > $offset_items
            ) {
                $primary_color = htmlspecialchars($post_data["colors"]["post_color_primary"] ?? '');
                $carouselItems .= '<li class="carousel-item">';
                $carouselItems .= '<a class="dm-post" href="' . htmlspecialchars($post_path) . '" style="--primary-color-post: ' . $primary_color . ';">';

                $carouselItems .= '<div class="device-layout-laptop">';
                $carouselItems .= '<div class="screen" ' . $render_bg_color . '>';
                $carouselItems .= render_image($web_image_path . $web_image, false, "web-desktop-image");
                if (!empty($render_bg_color)) {
                    $carouselItems .= '<div class="shadow-color" ' . $render_bg_color . '></div>';
                }
                $carouselItems .= '</div>';
                $carouselItems .= render_image($device_layout_img_path . $device_layout_laptop_img, false, "laptop");
                $carouselItems .= '</div>';

                if ($have_web_phone_image) {
                    $carouselItems .= '<div class="device-layout-phone">';
                    $carouselItems .= '<div class="screen" ' . $render_bg_color . '>';
                    $carouselItems .= render_image($web_phone_image_path . $phone_image, false, "web-phone-image");
                    if (!empty($render_bg_color)) {
                        $carouselItems .= '<div class="shadow-color" ' . $render_bg_color . '></div>';
                    }
                    $carouselItems .= '</div>';
                    $carouselItems .= render_image($device_layout_img_path . $device_layout_phone_img, false, "phone");
                    $carouselItems .= '</div>';
                }

                $carouselItems .= '</a>';
                $carouselItems .= '</li>';
                $nr_item++;
            }
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace(
            ['{{ direction }}', '{{ speed }}', '{{ carousel_items }}'],
            [htmlspecialchars($carousel_direction), htmlspecialchars($carousel_speed), $carouselItems],
            $template
        );
    }
}
