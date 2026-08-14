<?php

class SectionPost
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $jsonPostProjectFields = $data['postProjectFields'] ?? (get_data_json('data_content_personal', 'data')["post_projects"]["fields"] ?? []);
        $jsonCategoriesData = $data['categoriesData'] ?? get_data_json('data_post_projects_terms', 'data');
        $jsonJobs = $data['jobs'] ?? get_data_json('data_items_jobs', 'data');
        $postData = $data['postCurrentData'] ?? $data;
        $urlPath = $GLOBALS['urlPath'];

        $inlineStyles = '';
        if (isset($postData["colors"]) && !empty($postData["colors"])) {
            $inlineStyles = '<style>:root{';
            foreach ($postData["colors"] as $name => $value) {
                $inlineStyles .= '--' . $name . ':' . $value . ';';
            }
            $inlineStyles .= '}</style>';
        }

        $postContent = '';
        if (isset($data['postContent'])) {
            $postContent = $data['postContent'];
        }

        $postImage = '';
        if (isset($postData["logo"]) && isset($postData["logo_path"])) {
            $logo = $urlPath . "src/content/img/" . $postData["post_type"] . "/" . $postData["logo_path"] . "/" . $postData["logo"];
            $thumbnailClass = (isset($postData["thumbnail"]) && isset($postData["thumbnail_path"])) ? "bg-thumbnail" : "";
            $postImage .= '<div class="post-image post-logo ' . $thumbnailClass . '" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.4s">';
            $postImage .= render_bg_img_overlay_texture();
            if (isset($postData["logo_type"]) && $postData["logo_type"] == "svg") {
                $postImage .= svg_get($postData["logo"]);
            } else {
                $postImage .= render_image($logo, false, false, true, [
                    "alt" => "Post Logo - " . $postData["title"],
                    "data-motion" => "transition-fade-0 transition-blur-0 transition-slideInBottom-0",
                    "data-duration" => "0.8s"
                ]);
            }
            if (isset($postData["thumbnail"]) && isset($postData["thumbnail_path"])) {
                $thumbnail = $urlPath . "src/content/img/" . $postData["post_type"] . "/" . $postData["thumbnail_path"] . "/" . $postData["thumbnail"];
                $postImage .= render_image($thumbnail, false, "thumbnail-overlay-for-logo", true, ["alt" => "Post Thumbnail - " . $postData["title"]]);
            }
            $postImage .= '</div>';
        } elseif (isset($postData["thumbnail"]) && isset($postData["thumbnail_path"])) {
            $thumbnail = $urlPath . "src/content/img/" . $postData["post_type"] . "/" . $postData["thumbnail_path"] . "/" . $postData["thumbnail"];
            $postImage .= '<div class="post-image post-thumbnail" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.4s">';
            $postImage .= render_image($thumbnail, false, false, true, ["alt" => "Post Thumbnail - " . $postData["title"]]);
            $postImage .= render_image($thumbnail, false, "overlay", true, ["alt" => "Post Thumbnail Overlay"]);
            $postImage .= '</div>';
        }

        $sidebarContent = '';

        if (isset($postData["title"]) && !empty($postData["title"])) {
            $sidebarContent .= '<h2 class="post-title">' . $postData["title"] . '</h2>';
        }

        if (isset($postData["description"]) && !empty($postData["description"])) {
            $sidebarContent .= '<p class="post-description">' . execute_php_in_string($postData["description"]) . '</p>';
        }

        if (isset($postData["categories"]) && !empty($postData["categories"])) {
            $sidebarContent .= '<div class="post-categories">';
            foreach ($postData["categories"] as $postCategory) {
                if ($postCategory == "Miscellaneous Projects") {
                    $sidebarContent .= '<span class="post-category">' . $postCategory . '</span>';
                } else {
                    $postCategorySlug = change_space_with_hyphen_and_lowercase($postCategory);
                    if (isset($jsonCategoriesData["categories"])) {
                        foreach ($jsonCategoriesData["categories"] as $category) {
                            if ($category["name"] === $postCategory) {
                                $postCategorySlug = $category["slug"];
                                break;
                            }
                        }
                    }
                    $sidebarContent .= '<a class="post-category" href="' . $postCategorySlug . $globalData["page_slug_extension"] . '">' . $postCategory . '</a>';
                }
            }
            $sidebarContent .= '</div>';
        }

        if (isset($postData["web_url"]) && !empty($postData["web_url"])) {
            $sidebarContent .= '<p class="post-website"><span>' . svg_get('web');
            if (isset($jsonPostProjectFields["web"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["web"] . '</span>';
            }
            $sidebarContent .= '</span><a href="' . add_https($postData["web_url"]) . '" target="_blank">' . remove_https($postData["web_url"]) . '</a></p>';
        }

        if (isset($postData["web_platform"]) && !empty($postData["web_platform"])) {
            $sidebarContent .= '<p class="post-website-platform">';
            if (isset($jsonPostProjectFields["web_platform"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["web_platform"] . '</span>';
            }
            $platformNames = array_map(function ($p) {
                return $p["name"];
            }, $postData["web_platform"]);
            $sidebarContent .= implode(", ", $platformNames);
            $sidebarContent .= '</p>';
        }

        if (isset($postData["web_technology"]) && !empty($postData["web_technology"])) {
            $sidebarContent .= '<p class="post-website-technology">';
            if (isset($jsonPostProjectFields["web_technology"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["web_technology"] . '</span>';
            }
            $techNames = array_map(function ($t) {
                return $t["name"];
            }, $postData["web_technology"]);
            $sidebarContent .= implode(", ", $techNames);
            $sidebarContent .= '</p>';
        }

        if (isset($postData["web_plugins"]) && !empty($postData["web_plugins"])) {
            $sidebarContent .= '<p class="post-website-modules">';
            if (isset($jsonPostProjectFields["web_plugins"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["web_plugins"] . '</span>';
            }
            $pluginNames = array_map(function ($p) {
                return $p["name"];
            }, $postData["web_plugins"]);
            $sidebarContent .= implode(", ", $pluginNames);
            $sidebarContent .= '</p>';
        }

        $iconsList = [];
        if (isset($postData["web_platform"]) && is_array($postData["web_platform"])) {
            $iconsList = array_merge($iconsList, $postData["web_platform"]);
        }
        if (isset($postData["web_technology"]) && is_array($postData["web_technology"])) {
            $iconsList = array_merge($iconsList, $postData["web_technology"]);
        }
        if (isset($postData["web_plugins"]) && is_array($postData["web_plugins"])) {
            $iconsList = array_merge($iconsList, $postData["web_plugins"]);
        }

        if (count($iconsList) > 0) {
            $sidebarContent .= '<ul class="post-website-icons">';
            foreach ($iconsList as $icon) {
                if (isset($icon["svg"]) && svg_has_icon($icon["svg"])) {
                    $sidebarContent .= '<li>' . svg_get($icon["svg"]) . '</li>';
                }
            }
            $sidebarContent .= '</ul>';
        }

        if (isset($postData["project_collaboration"]) && !empty($postData["project_collaboration"])) {
            $sidebarContent .= '<p class="post-custom-field-text">';
            if (isset($jsonPostProjectFields["project_collaboration"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["project_collaboration"] . '</span>';
            }
            $sidebarContent .= implode(", ", $postData["project_collaboration"]);
            $sidebarContent .= '</p>';
        }

        if (isset($postData["web_project_status"]) && !empty($postData["web_project_status"])) {
            $sidebarContent .= '<div class="post-website-status">';
            if (isset($jsonPostProjectFields["web_project_status"])) {
                $sidebarContent .= '<span class="label">' . $jsonPostProjectFields["web_project_status"] . '</span>';
            }
            $sidebarContent .= '<span class="status ' . strtolower($postData["web_project_status"]) . '">' . $postData["web_project_status"] . '</span>';
            $sidebarContent .= '</div>';
        }

        if (isset($postData["media_facebook_url"]) && !empty($postData["media_facebook_url"])) {
            $sidebarContent .= '<p class="post-media-facebook">';
            $sidebarContent .= '<a href="' . add_https($postData["media_facebook_url"]) . '" target="_blank">' . svg_get('socials-facebook') . '</a>';
            if (isset($jsonPostProjectFields["media_facebook_url"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["media_facebook_url"] . '</span>';
            }
            $sidebarContent .= '<a href="' . add_https($postData["media_facebook_url"]) . '" target="_blank">' . remove_https($postData["media_facebook_url"]) . '</a>';
            $sidebarContent .= '</p>';
        }

        if (isset($postData["media_instagram_url"]) && !empty($postData["media_instagram_url"])) {
            $sidebarContent .= '<p class="post-media-instagram">';
            $sidebarContent .= '<a href="' . add_https($postData["media_instagram_url"]) . '" target="_blank">' . svg_get('socials-instagram') . '</a>';
            if (isset($jsonPostProjectFields["media_instagram_url"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["media_instagram_url"] . '</span>';
            }
            $sidebarContent .= '<a href="' . add_https($postData["media_instagram_url"]) . '" target="_blank">' . remove_https($postData["media_instagram_url"]) . '</a>';
            $sidebarContent .= '</p>';
        }

        if (isset($postData["media_youtube_url"]) && !empty($postData["media_youtube_url"])) {
            $sidebarContent .= '<p class="post-media-facebook">';
            $sidebarContent .= '<a href="' . add_https($postData["media_youtube_url"]) . '" target="_blank">' . svg_get('socials-youtube') . '</a>';
            if (isset($jsonPostProjectFields["media_youtube_url"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["media_youtube_url"] . '</span>';
            }
            $sidebarContent .= '<a href="' . add_https($postData["media_youtube_url"]) . '" target="_blank">' . remove_https($postData["media_youtube_url"]) . '</a>';
            $sidebarContent .= '</p>';
        }

        if (isset($postData["media_custom"]) && !empty($postData["media_custom"])) {
            $sidebarContent .= '<ul class="post-media-custom">';
            foreach ($postData["media_custom"] as $mediaCustom) {
                $sidebarContent .= '<li>' . svg_get('chevron-right') . '<span>';
                ob_start();
                check_echo($mediaCustom["title"]);
                $sidebarContent .= ob_get_clean();
                $sidebarContent .= '</span><a href="' . add_https($mediaCustom["url"]) . '" target="_blank">' . remove_https($mediaCustom["url"]) . '</a></li>';
            }
            $sidebarContent .= '</ul>';
        }

        if (isset($postData["media_platforms"]) && !empty($postData["media_platforms"]) && count($postData["media_platforms"]) > 0) {
            $sidebarContent .= '<p class="post-media-platforms">';
            if (isset($jsonPostProjectFields["media_platforms"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["media_platforms"] . '</span>';
            }
            $platformNames = array_map(function ($p) {
                return $p["name"];
            }, $postData["media_platforms"]);
            $sidebarContent .= implode(", ", $platformNames);
            $sidebarContent .= '</p>';
            $sidebarContent .= '<ul class="post-media-icons">';
            foreach ($postData["media_platforms"] as $icon) {
                if (isset($icon["svg"]) && svg_has_icon($icon["svg"])) {
                    $sidebarContent .= '<li>' . svg_get($icon["svg"]) . '</li>';
                }
            }
            $sidebarContent .= '</ul>';
        }

        if (isset($postData["project_types"]) && !empty($postData["project_types"])) {
            $sidebarContent .= '<div class="post-categories">';
            foreach ($postData["project_types"] as $postType) {
                $sidebarContent .= '<span class="post-tag">' . $postType . '</span>';
            }
            $sidebarContent .= '</div>';
        }

        if (isset($postData["employer"]) && !empty($postData["employer"])) {
            $sidebarContent .= '<div class="post-employ">';
            if ($postData["employer"] == "Freelancer") {
                if (isset($jsonPostProjectFields["employer_type"])) {
                    $sidebarContent .= '<span>' . $jsonPostProjectFields["employer_type"] . '</span>';
                }
                $sidebarContent .= '<a href="denismarginas' . $globalData["page_slug_extension"] . '" target="_blank">' . $postData["employer"] . '</a>';
            } else {
                if (isset($jsonPostProjectFields["employer_location"])) {
                    $sidebarContent .= '<span>' . $jsonPostProjectFields["employer_location"] . '</span>';
                }
                $sidebarContent .= '<a href="employee-experience' . $globalData["page_slug_extension"] . '#' . strtolower(str_replace(" ", "-", $postData["employer"])) . '" target="_blank">' . $postData["employer"] . '</a>';
                if (isset($jsonJobs)) {
                    foreach ($jsonJobs as $job) {
                        if (strtolower($job["name"]) == strtolower($postData["employer"]) && isset($job["img"]) && isset($job["display"]) && $job["display"] == "true") {
                            $imgBg = isset($job["img_bg"]) ? $job["img_bg"] : '';
                            $sidebarContent .= '<div class="work-logo ' . $imgBg . '">' . render_image($urlPath . $job["img"], false, false, true) . '</div>';
                        }
                    }
                }
            }
            $sidebarContent .= '</div>';
        }

        if (isset($postData["date"]) && !empty($postData["date"])) {
            $sidebarContent .= '<p class="post-data">';
            if (isset($jsonPostProjectFields["date"])) {
                $sidebarContent .= '<span>' . $jsonPostProjectFields["date"] . '</span>';
            }
            $sidebarContent .= $postData["date"]["start"] . ' - ' . $postData["date"]["end"];
            $sidebarContent .= '</p>';
        }

        if (isset($postData["tags"]) && !empty($postData["tags"])) {
            $sidebarContent .= '<div class="post-tags">';
            foreach ($postData["tags"] as $postTag) {
                $sidebarContent .= '<a class="post-tag" href="#' . remove_space_and_lowercase($postTag) . '">' . $postTag . '</a>';
            }
            $sidebarContent .= '</div>';
        }

        return self::render_template([
            'inline_styles' => $inlineStyles,
            'post_content' => $postContent,
            'post_image' => $postImage,
            'sidebar_content' => $sidebarContent,
        ]);
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
