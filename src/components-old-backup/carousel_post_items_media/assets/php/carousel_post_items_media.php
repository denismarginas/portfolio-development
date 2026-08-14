<?php

class CarouselPostItemsMedia
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $posts = $data['posts'] ?? get_data_json('index_data_post_projects', 'index');
        $carousel_direction = $data['carouselDirection'] ?? "right";
        $carousel_speed = $data['carouselSpeed'] ?? "slow";
        $offset_items = $data['offsetItems'] ?? 0;
        $max_items = $data['maxItems'] ?? 12;

        usort($posts, "dateStartPostSortDesc");
        usort($posts, "personalTypePostProjectSortAsc");

        if (count($posts) < 1 || empty($posts)) {
            return '';
        }

        $carouselItems = '';
        $nr_item = 1;
        foreach ($posts as $post) {
            $post_path = $post["post_id"] . $jsonGlobalData["page_slug_extension"];

            $post_data = flattenPostIndex($post);
            $has_logo = isset($post_data["logo"]) && isset($post_data["logo_path"]);
            $has_thumbnail = isset($post_data["thumbnail"]) && isset($post_data["thumbnail_path"]);

            if (!$has_logo && !$has_thumbnail) {
                continue;
            }

            if (
                isset($post_data["categories"]) &&
                in_array("Visual Media Projects", $post_data["categories"]) &&
                !in_array("Miscellaneous Projects", $post_data["categories"]) &&
                $max_items >= $nr_item &&
                $nr_item > $offset_items
            ) {
                $bg_color = htmlspecialchars($post_data["colors"]["post_color_background"] ?? '');
                $carouselItems .= '<li class="carousel-item">';
                $carouselItems .= '<a class="dm-post-item-image" href="' . htmlspecialchars($post_path) . '" style="background-color: ' . $bg_color . ';">';

                if ($has_logo) {
                    $logo = $GLOBALS['urlPath'] . "src/content/img/" . $post_data["post_type"] . "/" . $post_data["logo_path"] . "/" . $post_data["logo"];
                    if (isset($post_data["logo_type"]) && !empty($post_data["logo_type"]) && $post_data["logo_type"] == "svg") {
                        $carouselItems .= svg_get($post_data["logo"]);
                    } else {
                        $carouselItems .= render_image($logo, false, "logo", true, ["alt" => "Post Logo - " . $post_data["title"]]);
                    }
                }

                if ($has_thumbnail) {
                    $thumbnail = $GLOBALS['urlPath'] . "src/content/img/" . $post_data["post_type"] . "/" . $post_data["thumbnail_path"] . "/" . $post_data["thumbnail"];
                    $carouselItems .= render_image($thumbnail, false, "thumbnail", true, ["alt" => "Post Thumbnail - " . $post_data["title"]]);
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
