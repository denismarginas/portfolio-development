<?php

class video
{
    public static function render(array $data = []): string
    {
        $src = (string) ($data['src'] ?? $data['video'] ?? $data['path'] ?? $data['media']['video']['video'] ?? '');
        if ($src === '') {
            $media = $data['media'] ?? [];
            if (is_array($media['video'] ?? null)) $src = (string) ($media['video']['video'] ?? '');
        }
        if ($src === '') return '';

        if (!PlatformUrlService::is_external_url($src)) {
            $candidates = [ltrim($src, '/')];
            if (!str_starts_with(ltrim($src, '/'), 'src/content/')) {
                $candidates[] = 'src/content/vid/' . ltrim($src, '/');
                $candidates[] = 'src/content/' . ltrim($src, '/');
            }
            $found = null;
            foreach ($candidates as $cand) {
                $abs = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT . '/' . $cand : $cand;
                if (file_exists($abs)) { $found = $cand; break; }
            }
            if ($found === null) return '';
            $src = rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . ltrim($found, '/');
        }

        $thumb = (string) ($data['thumbnail'] ?? $data['video_thumbnail'] ?? $data['media']['video']['video_thumbnail'] ?? '');
        $thumbBg = (string) ($data['thumbnail_bg'] ?? '');
        $thumbImg = '';
        $thumbBgStyle = '';
        if ($thumb !== '') {
            $thumbImg = PlatformComponentRenderer::render('image', ['src' => $thumb, 'alt' => 'Video thumbnail']);
            if ($thumbImg === '') $thumbImg = '<img src="' . htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8') . '" alt="Video thumbnail">';
            if ($thumbBg !== '') {
                $thumbBgStyle = 'background-image: url(\'' . htmlspecialchars($thumbBg, ENT_QUOTES, 'UTF-8') . '\')';
            }
        }

        $color = (string) ($data['appearance']['colors']['primary'] ?? $data['video_primary_color'] ?? $data['primary'] ?? '#fff');
        if ($color === '') $color = '#fff';

        return PlatformTemplateRenderer::render([
            'src' => htmlspecialchars($src, ENT_QUOTES, 'UTF-8'),
            'thumbnail_img' => $thumbImg,
            'thumbnail_bg_style' => $thumbBgStyle,
            'no_thumbnail_class' => $thumb === '' ? ' no-thumbnail' : '',
            'style' => '--dm-color-primary:' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ';',
        ]);
    }
}