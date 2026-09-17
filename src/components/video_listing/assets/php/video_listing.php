<?php

class video_listing
{
    public static function render(array $data = []): string
    {
        $template = $data['template'] ?? [];
        $comp = str_replace('-', '_', (string) ($template['component'] ?? 'video'));
        $filterTags = $data['filter']['taxonomy']['tag'] ?? null;

        $videos = PlatformDataService::get_all_items_from_file('video') ?? [];
        $videos = array_values(array_filter($videos, fn($v) => ($v['settings']['render'] ?? true) !== false));

        if (is_array($filterTags) && !empty($filterTags)) {
            $videos = array_values(array_filter($videos, function ($v) use ($filterTags) {
                $tags = $v['taxonomy']['tag'] ?? $v['data']['taxonomy']['tag'] ?? [];
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
            $buttons = $d['media']['buttons'] ?? [];

            $textHtml = '<div class="video-text">';
            $textHtml .= PlatformComponentRenderer::render('text_block', ['elements' => [
                ['subtitle' => $title],
                ['paragraph' => $desc, 'class' => 'video-description', 'escape' => false],
            ]]);
            if (is_array($timeline) && !empty($timeline['list'])) {
                $textHtml .= '<div class="video-timeline"><span>' . htmlspecialchars($timeline['text'] ?? '', ENT_QUOTES, 'UTF-8') . '</span><ul>';
                foreach ($timeline['list'] as $tl) $textHtml .= '<li>' . htmlspecialchars((string) $tl, ENT_QUOTES, 'UTF-8') . '</li>';
                $textHtml .= '</ul></div>';
            }
            if (!empty($buttons) && is_array($buttons)) {
                $buttonItems = array_map(fn($b) => [
                    'text' => $b['text'] ?? '',
                    'link' => $b['url'] ?? '',
                    'svg' => $b['svg'] ?? '',
                ], $buttons);
                $textHtml .= PlatformComponentRenderer::render('text_block', ['elements' => [
                    ['buttons' => $buttonItems, 'class' => 'content-wrapper'],
                ]]);
            }
            $textHtml .= '</div>';

            $isReverse = $idx % 2 === 1;
            $itemsHtml .= '<div class="video-listing-item' . ($isReverse ? ' reverse' : '') . '"><div class="video-wrapper">' . $videoHtml . '</div>' . $textHtml . '</div>';
        }

        return PlatformTemplateRenderer::render(['items' => $itemsHtml]);
    }
}
