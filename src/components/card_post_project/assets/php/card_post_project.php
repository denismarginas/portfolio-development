<?php


class card_post_project
{
    private const CATEGORY_WEBSITE = 'Web Development Projects';
    private const CATEGORY_MEDIA = 'Visual Media Projects';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $seo = is_array($postData['seo'] ?? null) ? $postData['seo'] : [];
        $categories = self::resolve_categories($postData);

        $visualComponent = self::resolve_visual_component(
            $data['visual_component'] ?? null,
            $categories
        );

        $visualParams = is_array($data['visual_params'] ?? null) ? $data['visual_params'] : [];
        $visualParams['post_current_data'] = $post;
        $visual = PlatformComponentRenderer::render($visualComponent, $visualParams);

        $link = PlatformPathService::post_link((string) ($post['_id'] ?? ''));
        $title = self::truncate((string) ($seo['title'] ?? ''), 20);
        $descriptionHtml = self::render_description($data, $seo);

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'visual' => $visual,
            'visual_type' => htmlspecialchars($visualComponent, ENT_QUOTES, 'UTF-8'),
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'description' => $descriptionHtml,
        ]);
    }

    private static function render_description(array $data, array $seo): string
    {
        $show = (bool) ($data['description'] ?? false);
        if (!$show) {
            return '';
        }

        $excerpt = self::truncate((string) ($seo['description'] ?? ''), 25);
        if ($excerpt === '') {
            return '';
        }

        return '<p class="card-description">' . htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    /**
     * @return string[]
     */
    private static function resolve_categories(array $postData): array
    {
        $categories = $postData['taxonomy']['category'] ?? [];
        return is_array($categories) ? $categories : [];
    }

    private static function resolve_visual_component(?string $override, array $categories): string
    {
        if (is_string($override) && $override !== '') {
            return $override;
        }
        if (in_array(self::CATEGORY_WEBSITE, $categories, true)) {
            return 'card_post_project_visual_website';
        }
        if (in_array(self::CATEGORY_MEDIA, $categories, true)) {
            return 'card_post_project_visual_media';
        }
        return 'card_post_project_visual_default';
    }

    public static function truncate(string $text, int $maxLength): string
    {
        $text = trim($text);
        if ($maxLength <= 3) {
            return mb_substr($text, 0, $maxLength);
        }
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $maxLength - 3)) . '...';
    }
}
