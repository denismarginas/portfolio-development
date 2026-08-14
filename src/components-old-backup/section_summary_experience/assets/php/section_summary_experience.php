<?php

class SectionSummaryExperience
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $postsSummary = $data['postsSummary'] ?? get_data_json('data_post_summary_projects', 'data');
        $urlPath = $GLOBALS['urlPath'];

        $styles = '--dm-color-primary: #454545 !important;';
        $styles .= '--dm-color-secondary: #232323; !important;';
        $styles .= '--dm-video-color-secondary: #232323 !important;';
        $styles .= '--dm-video-color-primary: #ffffff !important;';
        $styles .= '--color-range-primary: #ffffff !important;';

        $heroComponent = render_component('hero', array_merge($data, ['heroData' => getDataHero("summary-experience")]));

        $summaryPosts = '';
        foreach ($postsSummary as $post) {
            $summaryPosts .= '<li class="dm-card-summary-project">';
            if (isset($post["category"])) {
                $summaryPosts .= '<span class="category">' . $post["category"] . '</span>';
            }
            if (isset($post["title"])) {
                $summaryPosts .= '<h4 class="title">' . $post["title"] . '</h4>';
            }
            if (isset($post["img_thumbnail"]) && isset($post["img_path"])) {
                $summaryPosts .= '<div class="image">';
                if (!empty($post["img_thumbnail"])) {
                    $summaryPosts .= render_image($urlPath . "src/content/img/" . $post["img_path"] . "/" . $post["img_thumbnail"]);
                } else {
                    $summaryPosts .= render_image($urlPath . "src/content/img/placeholder/img-placeholder.webp");
                }
                $summaryPosts .= '</div>';
            }
            if (isset($post["content"])) {
                $summaryPosts .= '<div class="content">';
                foreach ($post["content"] as $contentItem) {
                    if (is_array($contentItem) && isset($contentItem['component'])) {
                        $summaryPosts .= render_component($contentItem['component'], $contentItem['data'] ?? []);
                    } elseif (is_string($contentItem) && str_contains($contentItem, '<?php')) {
                        $summaryPosts .= execute_php_in_string($contentItem);
                    } elseif (is_string($contentItem)) {
                        $summaryPosts .= $contentItem;
                    }
                }
                $summaryPosts .= '</div>';
            }
            $summaryPosts .= '</li>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ styles }}', '{{ hero_component }}', '{{ summary_posts }}'],
            [$styles, $heroComponent, $summaryPosts],
            $template
        );
    }
}
