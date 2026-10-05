<?php

/**
 *   { "component": "social_links", "data": { "title": "Socials", "lists": ["visual", "text"] } }
 *
 * data:
 *   type       string  item file (default "social_link" -> data_items_social_link.json)
 *   lists      array   which lists to show, in order: "visual" (icon row) and/or "text" (default both)
 *   title      string  optional heading
 *   title_tag  string  "h3" (default) | "h2" | "h4" | "h5"
 *   variant    string  "card" (default, boxed like the contact page) | "plain" (no box, e.g. footer)
 *   class, id  string  optional
 *
 * Item (data_items_social_link.json):
 *   settings.render, data.seo.title, data.external_link, data.svg,
 *   data.visual_list.render, data.text_list.{ render, title, text, external_link }
 */
class social_links
{
    private const LISTS = ['visual', 'text'];
    private const TITLE_TAGS = ['h2', 'h3', 'h4', 'h5'];

    public static function render(array $data = []): string
    {
        $type = trim((string) ($data['type'] ?? '')) ?: 'social_link';
        $items = PlatformDataService::get_all_items_from_file($type) ?? [];
        if (!is_array($items)) return '';
        $items = array_values(array_filter($items, fn ($i) => is_array($i) && ($i['settings']['render'] ?? true) !== false));

        $lists = array_values(array_intersect((array) ($data['lists'] ?? self::LISTS), self::LISTS));

        $visual = in_array('visual', $lists, true) ? self::visual($items) : '';
        $text = in_array('text', $lists, true) ? self::text($items) : '';
        if ($visual === '' && $text === '') return '';

        // keep the requested order
        if ($lists === ['text', 'visual']) [$visual, $text] = [$text, $visual];

        $title = trim((string) ($data['title'] ?? ''));
        $tag = strtolower((string) ($data['title_tag'] ?? 'h3'));
        if (!in_array($tag, self::TITLE_TAGS, true)) $tag = 'h3';

        $class = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'variant' => strtolower((string) ($data['variant'] ?? 'card')) === 'plain' ? 'plain' : 'card',
            'title' => $title !== '' ? '<' . $tag . ' class="social-links-title">' . self::e($title) . '</' . $tag . '>' : '',
            'visual' => $visual,
            'text' => $text,
        ]);
    }

    private static function visual(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            $d = $item['data'] ?? [];
            if (($d['visual_list']['render'] ?? false) !== true) continue;
            $link = trim((string) ($d['external_link'] ?? ''));
            if ($link === '') continue;

            $title = trim((string) ($d['seo']['title'] ?? ''));
            $icon = (string) PlatformComponentRenderer::render('svg', [
                'icon' => (string) ($d['svg'] ?? ''),
                'class' => 'social-links-icon',
            ]);

            $html .= '<li><a class="social-links-visual-link" href="' . self::e($link) . '" target="_blank" rel="noopener" title="' . self::e($title) . '" aria-label="' . self::e($title) . '">'
                . ($icon !== '' ? $icon : '<span>' . self::e($title) . '</span>') . '</a></li>';
        }
        return $html === '' ? '' : '<ul class="social-links-visual">' . $html . '</ul>';
    }

    private static function text(array $items): string
    {
        $html = '';
        foreach ($items as $item) {
            $d = $item['data'] ?? [];
            $t = is_array($d['text_list'] ?? null) ? $d['text_list'] : [];
            if (($t['render'] ?? false) !== true) continue;

            $link = trim((string) ($t['external_link'] ?? $d['external_link'] ?? ''));
            if ($link === '') continue;
            $title = trim((string) ($t['title'] ?? $d['seo']['title'] ?? ''));
            $text = trim((string) ($t['text'] ?? preg_replace('#^https?://(www\.)?#', '', rtrim($link, '/'))));

            $html .= '<li><a class="social-links-text-link" href="' . self::e($link) . '" target="_blank" rel="noopener">'
                . '<span class="social-links-dot" aria-hidden="true"></span>'
                . '<b>' . self::e($title) . '</b>'
                . '<span class="social-links-text-value">' . self::e($text) . '</span></a></li>';
        }
        return $html === '' ? '' : '<ul class="social-links-text">' . $html . '</ul>';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
