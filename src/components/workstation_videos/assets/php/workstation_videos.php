<?php

class WorkstationVideos
{
    public static function render(array $data = []): string
    {
        $postData = $data['postData'] ?? [];
        $layout = $data['layout'] ?? null;

        if (!isset($postData['videos'])) {
            return '';
        }

        $videoMediaPath = $postData["path_vid"] . "/" ?? "";

        $videoItems = '';
        $index = 1;
        foreach ($postData['videos'] as $video) {
            $videoItems .= '<ul>';
            $videoItems .= '<li class="text-content">';
            $videoItems .= '<h2 class="title" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.6s">';
            $videoItems .= $video["title"];
            $videoItems .= '</h2>';
            $videoItems .= '<span class="date" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.6s">';
            $videoItems .= $video["date"];
            $videoItems .= '</span>';
            $videoItems .= '<div class="description" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s">';
            $videoItems .= $video["description"];
            $videoItems .= '</div>';
            $videoItems .= '</li>';
            $videoItems .= '<li id="video-' . $index . '" class="media-content" data-motion="transition-fade-0 transition-slideInLeft-0">';
            $videoItems .= render_component('video', [
                'src' => $GLOBALS['urlPath'] . 'content/vid/' . $videoMediaPath . $video['video_path'],
                'thumbnail' => $GLOBALS['urlPath'] . 'content/img' . $video['video_thumbnail'],
                'thumbnail_bg' => $GLOBALS['urlPath'] . 'content/img/thumbnails/workpreview-overlay-thumbnail.webp',
                'thumbnail_alt' => $video['title'] ?? 'Video thumbnail'
            ]);
            $videoItems .= '</li>';
            $videoItems .= '</ul>';
            $index++;
        }

        $wavesContent = '';
        if (isset($layout) && !empty($layout) && $layout == "default") {
            $wavesContent .= '<svg class="wave-shape primary" viewBox="0 0 1440 320">';
            $wavesContent .= '<path fill="var(--w-color-primary)" fill-opacity="1" d="M0,128L40,149.3C80,171,160,213,240,208C320,203,400,149,480,138.7C560,128,640,160,720,181.3C800,203,880,213,960,213.3C1040,213,1120,203,1200,197.3C1280,192,1360,192,1400,192L1440,192L1440,320L1400,320C1360,320,1280,320,1200,320C1120,320,1040,320,960,320C880,320,800,320,720,320C640,320,560,320,480,320C400,320,320,320,240,320C160,320,80,320,40,320L0,320Z"></path>';
            $wavesContent .= '</svg>';
            $wavesContent .= '<svg class="wave-shape secondary" viewBox="0 0 1440 320">';
            $wavesContent .= '<path fill="var(--w-color-secondary)" fill-opacity="1" d="M0,96L34.3,112C68.6,128,137,160,206,181.3C274.3,203,343,213,411,218.7C480,224,549,224,617,208C685.7,192,754,160,823,176C891.4,192,960,256,1029,261.3C1097.1,267,1166,213,1234,208C1302.9,203,1371,245,1406,266.7L1440,288L1440,320L1405.7,320C1371.4,320,1303,320,1234,320C1165.7,320,1097,320,1029,320C960,320,891,320,823,320C754.3,320,686,320,617,320C548.6,320,480,320,411,320C342.9,320,274,320,206,320C137.1,320,69,320,34,320L0,320Z"></path>';
            $wavesContent .= '</svg>';
            $wavesContent .= '<svg class="wave-shape tertiary" viewBox="0 0 1440 320">';
            $wavesContent .= '<path fill="var(--w-color-primary)" fill-opacity="1" d="M0,128L40,112C80,96,160,64,240,58.7C320,53,400,75,480,69.3C560,64,640,32,720,21.3C800,11,880,21,960,53.3C1040,85,1120,139,1200,165.3C1280,192,1360,192,1400,192L1440,192L1440,0L1400,0C1360,0,1280,0,1200,0C1120,0,1040,0,960,0C880,0,800,0,720,0C640,0,560,0,480,0C400,0,320,0,240,0C160,0,80,0,40,0L0,0Z"></path>';
            $wavesContent .= '</svg>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ video_items }}', '{{ waves_content }}'],
            [$videoItems, $wavesContent],
            $template
        );
    }
}
