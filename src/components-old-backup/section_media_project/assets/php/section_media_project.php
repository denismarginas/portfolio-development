<?php

class SectionMediaProject
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $params = $data['params'] ?? [];
        $tags = $postData['tags'] ?? [];
        $categories = $postData['categories'] ?? [];
        $content = '';

        if (in_array('photo', $tags) || in_array('video', $tags)) {
            $content .= ComponentRenderer::render_component('title', ['text' => 'Visual Media']);
        }

        if (in_array('Visual Media Projects', $categories) && in_array('Web Development Projects', $categories)) {
            $content .= self::render_details($postData);
        }

        if (in_array('photo', $tags)) {
            $content .= ComponentRenderer::render_component('gallery_media', ['post_current_data' => $postData]);
        }

        if (in_array('video', $tags)) {
            $content .= ComponentRenderer::render_component('video_media', [
                'post_current_data' => $postData,
                'videos' => $params['videos'] ?? null,
            ]);
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ content }}', $content, $template);
    }

    protected static function render_details(array $postData): string
    {
        $urlPath = DataService::get_url_path();
        $postType = $postData['post_type'] ?? 'projects';
        $mediaPath = $postData['media_path'] ?? '';
        $logo = $postData['logo'] ?? '';

        $html = '<div class="dm-post-details-grid">';
        $html .= '<div class="dm-post-logo-details" data-motion="transition-fade-0" data-duration="0.7s">';
        $html .= ComponentRenderer::render_component('image', [
            'src' => $urlPath . 'src/content/img/' . $postType . '/' . $mediaPath . '/' . $logo,
        ]);
        $html .= '</div>';
        $html .= '<div class="dm-post-title-description" data-motion="transition-fade-0" data-duration="0.7s">';
        $html .= self::render_text($postType, $mediaPath);
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected static function render_text(string $postType, string $mediaPath): string
    {
        $srcCurrent = realpath(__DIR__ . '/../../../../');
        $imgPath = $srcCurrent . '/content/img/' . $postType . '/' . $mediaPath . '/media/';
        $vidPath = $srcCurrent . '/content/vid/' . $postType . '/' . $mediaPath . '/';

        $nrImg = is_dir($imgPath) ? Helpers::countFilesInFolder($imgPath) : 0;
        $nrVid = is_dir($vidPath) ? Helpers::countFilesInFolder($vidPath) : 0;

        $parts = [];
        if ($nrImg > 0 || $nrVid > 0) {
            $parts[] = 'of';
        }
        if ($nrImg > 0) {
            $parts[] = $nrImg . ($nrImg > 1 ? ' photos' : ' photo');
        }
        if ($nrImg > 0 && $nrVid > 0) {
            $parts[] = 'and';
        }
        if ($nrVid > 0) {
            $parts[] = $nrVid . ($nrVid > 1 ? ' videos' : ' video');
        }

        $mediaString = implode(' ', $parts);
        if (empty($mediaString)) {
            $mediaString = 'content';
        }

        $andText = ($nrImg > 0 && $nrVid > 0)
            ? ' From stunning imagery to engaging videos, each piece is meticulously created to elevate your brand\'s online presence.'
            : '';

        $text = "Experience the artistry behind my meticulously crafted media content for this company. Discover a captivating collection {$mediaString} that showcase my expertise in delivering impactful visuals for social media promotion.{$andText} Explore the power of my unique visual creations and unlock the potential to captivate your audience.";

        $tags = '';
        if ($nrImg > 0) {
            $tags .= '<a class="post-tag" href="#photo">photo</a>';
        }
        if ($nrVid > 0) {
            $tags .= '<a class="post-tag" href="#video">video</a>';
        }

        return '<p>' . htmlspecialchars($text) . '</p>'
            . (!empty($tags) ? '<div class="post-tags">' . $tags . '</div>' : '');
    }
}