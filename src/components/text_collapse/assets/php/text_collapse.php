<?php

/**
 *   { "component": "text_collapse", "data": { "text": "Long text ...", "max_chars": 300 } }
 *   PlatformComponentRenderer::render('text_collapse', ['text' => $html, 'class' => 'video-description']);
 *
 * data:
 *   text        string  the text (may contain html, e.g. <br>, <b>, links)
 *   max_chars   int     longer texts are shortened to max_chars - 3 characters + "..." (default 300)
 *   escape      bool    true = escape the text (plain text); false (default) = output it as html
 *   more_label  string  default "Show More"
 *   less_label  string  default "Show Less"
 *   icon        string  svg icon of the button (src/content/svg, default "eye" - same as the toggle-preview button); "" = none
 *   class       string  optional extra class on the wrapper (e.g. the class the text had before)
 *
 * The character count uses the visible text (tags removed, <br> as line breaks). The short version is
 * plain text (line breaks kept), the full version is the original text. Texts within the limit render
 * as <div class="text-collapse text-collapse-static">text</div>, without a button.
 * Button: .text-collapse-toggle - post colors (--post-color-primary / -secondary) on a transparent
 * primary background with a 1px primary border (theme fallbacks outside a post).
 * Toggling: assets/js/text_collapse.js.
 */
class text_collapse
{
    public static function render(array $data = []): string
    {
        $raw = is_scalar($data['text'] ?? null) ? trim((string) $data['text']) : '';
        if ($raw === '') return '';

        $escape = ($data['escape'] ?? false) === true;
        $full = $escape ? nl2br(self::e($raw)) : $raw;
        $max = max(10, (int) ($data['max_chars'] ?? 300));
        $class = is_scalar($data['class'] ?? null) ? trim((string) $data['class']) : '';
        $extra = $class !== '' ? ' ' . self::e($class) : '';

        $plain = self::plain($raw, !$escape);
        if (mb_strlen($plain) <= $max) {
            return '<div class="text-collapse text-collapse-static' . $extra . '">' . $full . '</div>';
        }

        $short = rtrim(mb_substr($plain, 0, $max - 3)) . '...';

        return PlatformTemplateRenderer::render([
            'extra_class' => $extra,
            'short' => nl2br(self::e($short)),
            'full' => $full,
            'icon' => self::icon($data),
            'more' => self::e(is_scalar($data['more_label'] ?? null) ? (string) $data['more_label'] : 'Show More'),
            'less' => self::e(is_scalar($data['less_label'] ?? null) ? (string) $data['less_label'] : 'Show Less'),
        ]);
    }

    /** Visible text: <br> and block ends -> line breaks, tags removed, entities decoded, spaces collapsed. */
    private static function plain(string $text, bool $isHtml): string
    {
        if ($isHtml) {
            $text = preg_replace('#<\s*/?\s*br\s*/?\s*>#i', "\n", $text);
            $text = preg_replace('#</\s*(p|div|li|ul|ol|h[1-6])\s*>#i', "\n", $text);
            $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        $text = preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", $text));
        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    /** Button icon through the svg component. */
    private static function icon(array $data): string
    {
        $name = array_key_exists('icon', $data) ? (is_scalar($data['icon']) ? trim((string) $data['icon']) : '') : 'eye';
        return $name !== '' ? (string) PlatformComponentRenderer::render('svg', ['icon' => $name, 'class' => 'text-collapse-toggle-icon']) : '';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
