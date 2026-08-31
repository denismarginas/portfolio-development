<?php

class video_listing
{
    public static function render(array $data = []): string
    {
        $template = $data['template'] ?? [];
        $comp = str_replace('-', '_', (string) ($template['component'] ?? 'video'));
        $filterTags = $data['filter']['taxonomy']['tags'] ?? null;

        $videos = PlatformDataService::get_all_items_from_file('videos') ?? [];
        $videos = array_values(array_filter($videos, fn($v) => ($v['settings']['render'] ?? true) !== false));

        if (is_array($filterTags) && !empty($filterTags)) {
            $videos = array_values(array_filter($videos, function ($v) use ($filterTags) {
                $tags = $v['taxonomy']['tags'] ?? $v['data']['taxonomy']['tags'] ?? [];
                return !empty(array_intersect($filterTags, (array) $tags));
            }));
        }

        $itemsHtml = '';
        foreach ($videos as $idx => $video) {
            $d = $video['data'] ?? [];
            $media = $d['media']['video'] ?? [];
            $videoSrc = $media['video'] ?? '';
            $thumb = $media['video_thumbnail'] ?? '';
            $appearance = $video['settings']['appearance'] ?? $d['appearance'] ?? [];
            $color = $appearance['colors']['primary'] ?? '#fff';

            $videoHtml = PlatformComponentRenderer::render($comp, [
                'src' => $videoSrc,
                'thumbnail' => $thumb,
                'appearance' => ['colors' => ['primary' => $color]],
            ]);

            $videoData = $media['video_data'] ?? [];
            $title = $videoData['title'] ?? $d['seo']['title'] ?? '';
            $desc = $videoData['description'] ?? '';
            $timeline = $videoData['timeline'] ?? null;
            $urls = $videoData['urls'] ?? $d['urls'] ?? [];

            $textHtml = '<div class="video-text">';
            if ($title !== '') $textHtml .= '<h3>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h3>';
            if ($desc !== '') $textHtml .= '<div class="video-description">' . $desc . '</div>';
            if (is_array($timeline) && !empty($timeline['list'])) {
                $textHtml .= '<div class="video-timeline"><span>' . htmlspecialchars($timeline['text'] ?? '', ENT_QUOTES, 'UTF-8') . '</span><ul>';
                foreach ($timeline['list'] as $tl) $textHtml .= '<li>' . htmlspecialchars((string) $tl, ENT_QUOTES, 'UTF-8') . '</li>';
                $textHtml .= '</ul></div>';
            }
            if (!empty($urls) && is_array($urls)) {
                $textHtml .= '<div class="video-urls">';
                foreach ($urls as $u) $textHtml .= PlatformComponentRenderer::render('button', ['text' => $u['text'] ?? '', 'link' => $u['url'] ?? '', 'svg' => $u['svg'] ?? '']);
                $textHtml .= '</div>';
            }
            $textHtml .= '</div>';

            $isReverse = $idx % 2 === 1;
            $itemsHtml .= '<div class="video-listing-item' . ($isReverse ? ' reverse' : '') . '"><div class="video-wrapper">' . $videoHtml . '</div>' . $textHtml . '</div>';
        }

        return PlatformTemplateRenderer::render(['items' => $itemsHtml]);
    }
}