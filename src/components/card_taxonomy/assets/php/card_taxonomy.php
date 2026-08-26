<?php

class card_taxonomy
{
    public static function render(array $data = []): string
    {
        $term = is_array($data['term'] ?? null) ? $data['term'] : [];
        if (empty($term)) return '';

        // Taxonomy data may be wrapped in a "data" sub-object (settings/seo/media)
        $payload = is_array($term['data'] ?? null) ? array_merge($term, $term['data']) : $term;
        $seo = is_array($payload['seo'] ?? null) ? $payload['seo'] : [];

        $link = PlatformPathService::post_link((string) ($term['_id'] ?? ''));
        $title = (string) ($seo['title'] ?? $payload['name'] ?? $payload['title'] ?? '');
        $description = (string) ($seo['description'] ?? $payload['description'] ?? '');

        $imgHtml = '';
        $featureImg = $payload['media']['feature_img'] ?? '';
        if ($featureImg !== '') {
            $imgHtml = PlatformComponentRenderer::render('image', ['src' => $featureImg, 'alt' => $title, 'class' => 'bg-img card-image']);
        }
        $overlay = (string) ($data['overlay'] ?? '');
        if ($overlay !== '') {
            $imgHtml .= PlatformComponentRenderer::render('image', ['src' => $overlay, 'alt' => '', 'class' => 'bg-img card-overlay']);
        }

        $iconHtml = '';
        $svg = (string) ($payload['media']['svg'] ?? '');
        if ($svg !== '') {
            $iconHtml = PlatformComponentRenderer::render('svg', ['icon' => $svg, 'class' => 'card-svg']);
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'images' => $imgHtml,
            'svg_wrapper' => '<div class="svg-wrapper">' . $iconHtml . '</div>',
            'text_wrapper' => '<div class="text-wrapper"><span class="card-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>'
                . '<p class="card-description">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</p></div>',
        ]);
    }
}