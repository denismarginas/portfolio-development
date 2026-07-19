<?php

class VideoMedia
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $videos = $data['videos'] ?? null;
        $urlPath = DataService::get_url_path();

        $postType = $postData['post_type'] ?? 'projects';
        $mediaPath = $postData['media_path'] ?? '';

        $videoContent = '';
        if ($videos !== null && is_array($videos)) {
            $videoContent = self::render_strict_videos($videos);
        } else {
            $videoContent = self::render_video_folder($postType, $mediaPath, $postData);
        }

        return self::render_template([
            'video_content' => $videoContent,
        ]);
    }

    protected static function render_video_folder(string $postType, string $mediaPath, array $postData): string
    {
        $urlPath = DataService::get_url_path();
        $srcCurrent = realpath(__DIR__ . '/../../../../content/vid/' . $postType . '/' . $mediaPath . '/');
        $videoPath = $urlPath . 'content/vid/' . $postType . '/' . $mediaPath . '/';
        $logoPath = $urlPath . 'content/img/' . $postType . '/' . $mediaPath . '/' . ($postData['logo'] ?? '');
        $thumbnailBg = $urlPath . 'content/img/thumbnails/workpreview-overlay-thumbnail.webp';

        if (!is_dir($srcCurrent)) {
            return '';
        }

        $html = '';
        $directories = glob($srcCurrent . '*', GLOB_ONLYDIR);
        foreach ($directories as $directory) {
            $dirName = basename($directory);
            $videoFiles = Helpers::getVideosInFolder($directory . '/');
            if (!empty($videoFiles)) {
                $html .= '<ul class="dm-media-video" data-list-design="' . Helpers::list_design(count($videoFiles)) . '">';
                foreach ($videoFiles as $video) {
                    $path = $videoPath . $dirName . '/' . $video;
                    $html .= '<li class="dm-media-video-item" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s">';
                    $html .= ComponentRenderer::render_component('video', [
                        'src' => $path,
                        'thumbnail' => $logoPath,
                        'thumbnail_bg' => $thumbnailBg,
                    ]);
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }
        }

        $rootVideos = Helpers::getVideosInFolder($srcCurrent . '/');
        if (!empty($rootVideos)) {
            $html .= '<ul class="dm-media-video" data-list-design="' . Helpers::list_design(count($rootVideos)) . '">';
            foreach ($rootVideos as $video) {
                $path = $videoPath . $video;
                $html .= '<li class="dm-media-video-item" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s">';
                $html .= ComponentRenderer::render_component('video', [
                    'src' => $path,
                    'thumbnail' => $logoPath,
                    'thumbnail_bg' => $thumbnailBg,
                ]);
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }

    protected static function render_strict_videos(array $videos): string
    {
        $urlPath = DataService::get_url_path();
        $html = '';
        foreach ($videos as $video) {
            $html .= ComponentRenderer::render_component('video', [
                'src' => $urlPath . 'content/vid/' . ($video['video_path'] ?? ''),
                'thumbnail' => $urlPath . 'content/img/' . ($video['video_thumbnail_path'] ?? ''),
            ]);
        }
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
