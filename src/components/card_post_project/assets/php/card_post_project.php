<?php


/**
 * Project card: visual (by category or data.visual_component) + title link (+ optional description).
 *
 * data:
 *   visual_component  string  force a visual component (default: by category)
 *   visual_params     object  params for the visual component
 *   description       bool    show the SEO description
 *   description_length int    max characters of the description (default 25; 0 = the full text)
 *   title_length      int     max characters of the title (default 20; 0 = the full title)
 *   card_class        string  classes of the card element
 *                             (default "card card-outline card-post card-post-project";
 *                              e.g. "card-post-project" for a card without its own card frame, inside another card)
 *   columns           object  visual and text side by side through the "columns" component, e.g.
 *                             { "layout": { "base": "100", "sm": "50-50", "md": "30-70", "lg": "40-60", "xl": "30-70" },
 *                               "gap": "sm" }
 *                             (columns params: layout, gap, align, class; default gap "sm", align "start" = top).
 *                             Adds the class "card-post-project-columns" to the card.
 */
class card_post_project
{
    private const CATEGORY_WEBSITE = 'Web Development Projects';
    private const CATEGORY_MEDIA = 'Visual Media Projects';
    private const CARD_CLASS = 'card-post-project';
    private const HTML_DIR = __DIR__ . '/../html/';

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
        $title = self::truncate((string) ($seo['title'] ?? ''), self::length($data, 'title_length', 20));
        $descriptionHtml = self::render_description($data, $seo);

        $visualHtml = PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/visual.html', ['visual' => $visual]);
        $bodyHtml = PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/body.html', [
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'description' => $descriptionHtml,
        ]);

        $cardClass = self::card_class($data);
        $columns = is_array($data['columns'] ?? null) ? $data['columns'] : null;
        if ($columns !== null) {
            $cardClass .= ' card-post-project-columns';
            $inner = self::columns($columns, $visualHtml, $bodyHtml);
        } else {
            $inner = $visualHtml . $bodyHtml;
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'card_class' => htmlspecialchars($cardClass, ENT_QUOTES, 'UTF-8'),
            'inner' => $inner,
            'visual_type' => htmlspecialchars($visualComponent, ENT_QUOTES, 'UTF-8'),
            // --post-color-* of this project (listing pages are outside section_layout_aside)
            'style_attr' => (string) PlatformComponentRenderer::value('utility_appearance_colors', [
                'post_current_data' => $post,
                'output' => 'attribute',
            ]),
        ]);
    }

    /** Visual + body as two columns of the "columns" component. */
    private static function columns(array $params, string $visualHtml, string $bodyHtml): string
    {
        $params = array_merge(['gap' => 'sm', 'align' => 'start'], $params);
        $params['class'] = trim('card-post-project-body-columns ' . (is_scalar($params['class'] ?? null) ? (string) $params['class'] : ''));
        $params['children'] = [
            ['html' => $visualHtml, 'class' => 'card-post-project-column-visual'],
            ['html' => $bodyHtml, 'class' => 'card-post-project-column-body'],
        ];
        return (string) PlatformComponentRenderer::render('columns', $params);
    }

    /** Integer param; 0 = no limit. */
    private static function length(array $data, string $key, int $default): int
    {
        $value = $data[$key] ?? $default;
        return is_numeric($value) ? max(0, (int) $value) : $default;
    }

    /** data.card_class (letters, digits, - and _ only) or the default card classes. */
    private static function card_class(array $data): string
    {
        $class = is_scalar($data['card_class'] ?? null) ? (string) $data['card_class'] : '';
        $class = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-zA-Z0-9_\- ]/', '', $class)));
        return $class !== '' ? $class : self::CARD_CLASS;
    }

    private static function render_description(array $data, array $seo): string
    {
        $show = (bool) ($data['description'] ?? false);
        if (!$show) {
            return '';
        }

        $excerpt = self::truncate((string) ($seo['description'] ?? ''), self::length($data, 'description_length', 25));
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
            $override = str_replace('-', '_', $override);
            // old name, kept working after the rename
            return $override === 'card_post_project_visual_website' ? 'card_post_project_visual_devices_website' : $override;
        }
        if (in_array(self::CATEGORY_WEBSITE, $categories, true)) {
            return 'card_post_project_visual_devices_website';
        }
        if (in_array(self::CATEGORY_MEDIA, $categories, true)) {
            return 'card_post_project_visual_media';
        }
        return 'card_post_project_visual_default';
    }

    public static function truncate(string $text, int $maxLength): string
    {
        $text = trim($text);
        if ($maxLength === 0) {
            return $text;
        }
        if ($maxLength <= 3) {
            return mb_substr($text, 0, $maxLength);
        }
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }
        return rtrim(mb_substr($text, 0, $maxLength - 3)) . '...';
    }
}
