<?php

class DevicesPostItemMedia
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $post_data = $data['postData'] ?? [];

        if (empty($post_data)) {
            return '';
        }

        $device_layout_img_path = $GLOBALS['urlPath'] . "content/img/" . "design-elements" . "/";
        $device_layout_tablet_img = "device-layout-tablet.webp";
        $device_layout_tablet_vid = "device-layout-tablet-landscape.webp";

        $have_photo_content = false;
        $have_video_content = false;
        $first_img = '';
        $first_vid = '';

        if (isset($post_data["post_type"]) && isset($post_data["media_path"])) {
            $media_image_path = $GLOBALS['urlPath'] . "content/img/" . $post_data["post_type"] . "/" . $post_data["media_path"] . "/media/";
            $abs_path = realpath(__DIR__ . "/../../../../../") . "/" . $media_image_path;

            if (is_dir($abs_path)) {
                $get_media_image = getImagesInFolder($abs_path);
                $dir_image_path = "";

                if (count($get_media_image) === 0) {
                    $dirs = getDirectoriesInFolder($abs_path);
                    foreach ($dirs as $dir) {
                        $get_media_image = getImagesInFolder($abs_path . $dir . "/");
                        if (count($get_media_image) > 0) {
                            $dir_image_path = $dir . "/";
                            break;
                        }
                    }
                }

                if (count($get_media_image) > 0) {
                    $photo_image = $get_media_image[0];
                    $first_img = $media_image_path . $dir_image_path . $photo_image;
                    $have_photo_content = true;
                }
            }

            $media_video_path = $GLOBALS['urlPath'] . "content/vid/" . $post_data["post_type"] . "/" . $post_data["media_path"] . "/";
            $abs_vid_path = realpath(__DIR__ . "/../../../../../") . "/" . $media_video_path;

            if (is_dir($abs_vid_path)) {
                $get_media_video = getVideosInFolder($abs_vid_path);
                $dir_video_path = "";

                if (count($get_media_video) === 0) {
                    $dirs = getDirectoriesInFolder($abs_vid_path);
                    foreach ($dirs as $dir) {
                        $get_media_video = getVideosInFolder($abs_vid_path . $dir . "/");
                        if (count($get_media_video) > 0) {
                            $dir_video_path = $dir . "/";
                            break;
                        }
                    }
                }

                if (count($get_media_video) > 0) {
                    $video_file_name = $get_media_video[0];
                    $first_vid = $media_video_path . $dir_video_path . $video_file_name;
                    $have_video_content = true;
                }
            }
        }

        $render_bg_color = '';
        if (isset($post_data["colors"]) && isset($post_data["colors"]["post_color_primary"])) {
            $render_bg_color = 'style="background-color: ' . htmlspecialchars($post_data["colors"]["post_color_primary"]) . '"';
        }

        $mediaItems = '';

        if ($have_photo_content && in_array("photo", $post_data["tags"])) {
            $primary_color = htmlspecialchars($post_data["colors"]["post_color_primary"] ?? '');
            $mediaItems .= '<div class="devices-item-media type-img">';
            $mediaItems .= '<div class="dm-post" style="--primary-color-post: ' . $primary_color . ';">';
            $mediaItems .= '<div class="device-layout-tablet">';
            $mediaItems .= '<div class="screen" ' . $render_bg_color . '>';
            $mediaItems .= render_image($first_img, false, "photo-image");
            if (!empty($render_bg_color)) {
                $mediaItems .= '<div class="shadow-color" ' . $render_bg_color . '></div>';
            }
            $mediaItems .= '</div>';
            $mediaItems .= render_image($device_layout_img_path . $device_layout_tablet_img, false, "tablet");
            $mediaItems .= '</div>';
            $mediaItems .= '</div>';
            $mediaItems .= '</div>';
        }

        if ($have_video_content && in_array("video", $post_data["tags"])) {
            $primary_color = htmlspecialchars($post_data["colors"]["post_color_primary"] ?? '');
            $mediaItems .= '<div class="devices-item-media type-vid">';
            $mediaItems .= '<div class="dm-post" style="--primary-color-post: ' . $primary_color . ';">';
            $mediaItems .= '<div class="device-layout-tablet-landscape">';
            $mediaItems .= '<div class="screen" ' . $render_bg_color . '>';
            $mediaItems .= svg_get("play");
            $mediaItems .= '<video src="' . htmlspecialchars($first_vid) . '"></video>';
            if (!empty($render_bg_color)) {
                $mediaItems .= '<div class="shadow-color" ' . $render_bg_color . '></div>';
            }
            $mediaItems .= '</div>';
            $mediaItems .= render_image($device_layout_img_path . $device_layout_tablet_vid, false, "tablet-landscape");
            $mediaItems .= '</div>';
            $mediaItems .= '</div>';
            $mediaItems .= '</div>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace('{{ media_items }}', $mediaItems, $template);
    }
}
