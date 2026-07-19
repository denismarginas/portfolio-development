<?php

class SectionPortfolioShowcase
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $jsonPortfolio = $data['portfolioData'] ?? get_data_json('data_content_personal', 'data')["portfolio-video-archive"];

        $portfolioItems = '';
        if (isset($jsonPortfolio) && !empty($jsonPortfolio)) {
            foreach ($jsonPortfolio as $video_item) {
                $portfolioItems .= '<ul';
                if (isset($video_item["video_data"]["video_color_primary"]) && !empty($video_item["video_data"]["video_color_primary"])) {
                    $portfolioItems .= ' style="--dm-video-color-primary: ' . $video_item["video_data"]["video_color_primary"] . '; --color-range-primary: ' . $video_item["video_data"]["video_color_primary"] . ';"';
                }
                $portfolioItems .= '>';
                $portfolioItems .= '<li data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.4s" data-delay="0s">';
                $portfolioItems .= renderVideo(
                    $GLOBALS['urlPath'] . 'content/vid/' . $video_item["video_path"] . $video_item["video"],
                    $GLOBALS['urlPath'] . "content/img" . $video_item["video_thumbnail_path"] . $video_item["video_thumbnail"]
                );
                $portfolioItems .= '</li>';
                $portfolioItems .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.5s" data-delay="0s">';
                $portfolioItems .= '<h2 class="video-title" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.7s" data-delay="0s">' . $video_item["video_data"]['title'] . '</h2>';
                $portfolioItems .= '<p class="video-date" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s" data-delay="0.05s">' . $video_item["video_data"]['date'] . '</p>';
                $portfolioItems .= '<p class="video-description" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s" data-delay="0.1s">' . $video_item["video_data"]['short_description'] . '</p>';
                if (isset($video_item["video_data"]["youtube_button_link"]) && isset($video_item["video_data"]["youtube_button_text"])) {
                    $portfolioItems .= '<a class="dm-watch-on-youtube" href="' . $video_item["video_data"]["youtube_button_link"] . '" target="_blank" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s" data-delay="0.2s">';
                    $portfolioItems .= '<span class="icon">';
                    ob_start();
                    svg_render($video_item["video_data"]["youtube_button_icon_svg"]);
                    $portfolioItems .= ob_get_clean();
                    $portfolioItems .= '</span>';
                    $portfolioItems .= '<span class="text">' . $video_item["video_data"]["youtube_button_text"] . '</span>';
                    $portfolioItems .= '</a>';
                }
                if (isset($video_item["video_data"]["timeline"]) && !empty($video_item["video_data"]["timeline"])) {
                    $portfolioItems .= '<div class="video-timeline">';
                    if (isset($video_item["video_data"]["timeline"]["text"])) {
                        $portfolioItems .= '<p data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.6s" data-delay="0s">' . $video_item["video_data"]["timeline"]["text"] . '</p>';
                    }
                    if (isset($video_item["video_data"]["timeline"]["list"])) {
                        $portfolioItems .= '<ul>';
                        $list_items = $video_item["video_data"]["timeline"]["list"];
                        $i = 1;
                        foreach ($list_items as $list_item) {
                            $portfolioItems .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.7s" data-delay="' . ($i * 0.1) . 's">';
                            ob_start();
                            svg_render('chevron-right');
                            $portfolioItems .= ob_get_clean();
                            $portfolioItems .= '<span>' . $list_item . '</span>';
                            $portfolioItems .= '</li>';
                            $i++;
                        }
                        $portfolioItems .= '</ul>';
                    }
                    $portfolioItems .= '</div>';
                }
                $portfolioItems .= '</li>';
                $portfolioItems .= '</ul>';
            }
        }

        return self::render_template([
            'portfolio_items' => $portfolioItems,
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
