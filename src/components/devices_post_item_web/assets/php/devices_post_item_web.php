<?php

class DevicesPostItemWeb
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $post_data = $data['postData'] ?? [];

        if (empty($post_data)) {
            return '';
        }

        $personal_data = get_data_json('data_content_personal', 'data')["post_projects"]["img"] ?? [];
        $device_layout_laptop_img = $personal_data["devices"]["post_laptop"] ?? "";
        $device_layout_phone_img = $personal_data["devices"]["post_phone"] ?? "";
        $device_layout_img_path = $GLOBALS['urlPath'];

        $have_web_desktop_image = false;
        $have_web_phone_image = false;
        $web_image_path = '';
        $web_phone_image_path = '';
        $web_image = '';
        $phone_image = '';

        if (isset($post_data["post_type"]) && isset($post_data["media_path"])) {
            $web_image_path = $GLOBALS['urlPath'] . "content/img/" . $post_data["post_type"] . "/" . $post_data["media_path"] . "/web/home/";
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
            $web_phone_image_path = $GLOBALS['urlPath'] . "content/img/" . $post_data["post_type"] . "/" . $post_data["media_path"] . "/web/phone/";
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

        if ($have_web_desktop_image == false || !in_array("web", $post_data["tags"])) {
            return '';
        }

        $primary_color = htmlspecialchars($post_data["colors"]["post_color_primary"] ?? '');

        $laptopContent = '';
        $laptopContent .= '<div class="device-layout-laptop">';
        $laptopContent .= '<div class="screen" ' . $render_bg_color . '>';
        $laptopContent .= render_image($web_image_path . $web_image, false, "web-desktop-image");
        if (!empty($render_bg_color)) {
            $laptopContent .= '<div class="shadow-color" ' . $render_bg_color . '></div>';
        }
        $laptopContent .= '</div>';
        $laptopContent .= render_image($device_layout_img_path . $device_layout_laptop_img, false, "laptop");
        $laptopContent .= '</div>';

        $phoneContent = '';
        if ($have_web_phone_image) {
            $phoneContent .= '<div class="device-layout-phone">';
            $phoneContent .= '<div class="screen" ' . $render_bg_color . '>';
            $phoneContent .= render_image($web_phone_image_path . $phone_image, false, "web-phone-image");
            if (!empty($render_bg_color)) {
                $phoneContent .= '<div class="shadow-color" ' . $render_bg_color . '></div>';
            }
            $phoneContent .= '</div>';
            $phoneContent .= render_image($device_layout_img_path . $device_layout_phone_img, false, "phone");
            $phoneContent .= '</div>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace(
            ['{{ primary_color }}', '{{ laptop_content }}', '{{ phone_content }}'],
            [$primary_color, $laptopContent, $phoneContent],
            $template
        );
    }
}
