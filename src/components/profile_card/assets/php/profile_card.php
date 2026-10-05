<?php

/**
 *   {
 *     "component": "profile_card",
 *     "data": {
 *       "img": "personal-images/dm-personal-image-0.webp",
 *       "image_position": "left",
 *       "image_style": "circle",
 *       "title": "Contact Data",
 *       "text": ["First paragraph.", "Second paragraph."],
 *       "details": [
 *         { "label": "Email:", "value": "me@mail.com", "link": "mailto:me@mail.com" },
 *         { "label": "Phone:", "value": "+40-74*******", "note": "Why it is hidden..." }
 *       ]
 *     }
 *   }
 *
 * data:
 *   img             string  image path (relative to src/content/img/ or a full "src/..." path)
 *   img_alt         string  alt text (default: title)
 *   image_position  string  "left" (default) | "right" | "top" (stacked); on phones the image is always on top
 *   image_style     string  "circle" (portrait popping out of a circle, hover animation)
 *                           | "rounded" (default, rounded rectangle)
 *   title           string  heading (h2 with the title divider)
 *   title_tag       string  "h2" (default) | "h3" | "h4"
 *   text            string|array  paragraph(s); <br>, <b>, <strong>, <i>, <em> are kept
 *   details         array   [{ label, value, link?, svg? (default "chevron-right"), tooltip? }]
 *                           tooltip = { "text": "...", "status": "warning" } (tooltip component on the value)
 *   class, id       string  optional
 */
class profile_card
{
    private const ALLOWED_TAGS = '<br><b><strong><i><em>';
    private const TITLE_TAGS = ['h2', 'h3', 'h4'];

    public static function render(array $data = []): string
    {
        $title = trim((string) ($data['title'] ?? ''));
        $text = self::text($data['text'] ?? '');
        $details = self::details($data['details'] ?? []);
        $media = self::media($data, $title, self::style($data));

        if ($title === '' && $text === '' && $details === '' && $media === '') return '';

        $tag = strtolower((string) ($data['title_tag'] ?? 'h2'));
        if (!in_array($tag, self::TITLE_TAGS, true)) $tag = 'h2';

        $position = strtolower((string) ($data['image_position'] ?? 'left'));
        if (!in_array($position, ['left', 'right', 'top'], true)) $position = 'left';
        $style = self::style($data);

        $class = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'image_position' => $position,
            'image_style' => $style,
            'media' => $media,
            'title' => $title !== '' ? '<' . $tag . ' class="profile-card-title title-divider">' . self::e($title) . '</' . $tag . '>' : '',
            'text' => $text,
            'details' => $details,
        ]);
    }

    private static function style(array $data): string
    {
        return strtolower((string) ($data['image_style'] ?? 'rounded')) === 'circle' ? 'circle' : 'rounded';
    }

    private static function media(array $data, string $title, string $style): string
    {
        $src = trim((string) ($data['img'] ?? ''));
        if ($src === '') return '';

        $img = (string) PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => (string) ($data['img_alt'] ?? $title),
            'class' => 'profile-card-image',
            'lazy' => true,
        ]);
        if (trim($img) === '') return '';

        if ($style === 'circle') {
            // circle background + portrait clipped by the circle's bottom half (head pops out on top)
            $img = '<span class="profile-card-circle"><span class="profile-card-circle-background"></span>'
                . '<span class="profile-card-circle-clip">' . $img . '</span></span>';
        }
        return '<div class="profile-card-media">' . $img . '</div>';
    }

    private static function text(mixed $text): string
    {
        $html = '';
        foreach ((array) $text as $paragraph) {
            $paragraph = trim(strip_tags((string) $paragraph, self::ALLOWED_TAGS));
            if ($paragraph !== '') $html .= '<p class="profile-card-text">' . $paragraph . '</p>';
        }
        return $html;
    }

    private static function details(mixed $details): string
    {
        if (!is_array($details) || empty($details)) return '';

        $html = '';
        foreach ($details as $detail) {
            if (!is_array($detail)) continue;
            $label = trim((string) ($detail['label'] ?? ''));
            $value = trim((string) ($detail['value'] ?? ''));
            if ($label === '' && $value === '') continue;

            $link = trim((string) ($detail['link'] ?? ''));
            $valueHtml = $link !== ''
                ? '<a class="profile-card-detail-value" href="' . self::e($link) . '"' . (preg_match('#^https?://#', $link) ? ' target="_blank" rel="noopener"' : '') . '>' . self::e($value) . '</a>'
                : '<span class="profile-card-detail-value">' . self::e($value) . '</span>';

            // tooltip on the value: { "text": "...", "status": "warning" } (also accepts a plain string)
            $tooltip = $detail['tooltip'] ?? $detail['note'] ?? null;
            if (is_string($tooltip)) $tooltip = ['text' => $tooltip, 'status' => 'warning'];
            if (is_array($tooltip) && isset($tooltip['data']) && is_array($tooltip['data'])) $tooltip = $tooltip['data'];
            if (is_array($tooltip) && trim((string) ($tooltip['text'] ?? '')) !== '') {
                $valueHtml = (string) PlatformComponentRenderer::render('tooltip', array_merge(
                    ['status' => 'warning', 'class' => 'profile-card-detail-tooltip'],
                    $tooltip,
                    ['trigger' => $valueHtml]
                ));
            }

            $icon = (string) PlatformComponentRenderer::render('svg', [
                'icon' => (string) ($detail['svg'] ?? 'chevron-right'),
                'class' => 'profile-card-detail-icon',
            ]);

            $html .= '<li class="profile-card-detail">' . $icon
                . '<b class="profile-card-detail-label">' . self::e($label) . '</b>'
                . $valueHtml . '</li>';
        }

        return $html === '' ? '' : '<ul class="profile-card-details">' . $html . '</ul>';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
