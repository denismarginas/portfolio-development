<?php

/**
 *   {
 *     "component": "knowledge_listing",
 *     "data": {
 *       "title": "Experience",
 *       "text": ["Intro paragraph..."],
 *       "groups": [ { "title": "Web Development", "list": ["Technology: PHP, ...", "..."] } ],
 *       "icons": true,
 *       "buttons": [ { "text": "Employee Experience", "_id": "employee-experience", "svg": "employ" } ]
 *     }
 *   }
 *
 * data:
 *   title       string  heading (h2)
 *   text        string|array  paragraph(s); <br>, <b>, <strong>, <i>, <em> are kept
 *   groups      array   [{ title, list: [...] }]
 *   icons       bool    show the knowledge icons (default true)
 *   icons_type  string  item file for the icons (default "knowledge_item" -> data_items_knowledge_item.json)
 *   icons_title string  optional label above the icons
 *   buttons     array   [{ text, svg, _id (page id) | link (URL), class }]
 *   class, id   string  optional
 *
 * Icon item: { _id, svg, text, external_url } (settings.render = false hides one).
 */
class knowledge_listing
{
    private const ALLOWED_TAGS = '<br><b><strong><i><em>';

    public static function render(array $data = []): string
    {
        $title = self::str($data['title'] ?? '');
        $html = [
            'title' => $title !== '' ? '<h2 class="knowledge-listing-title">' . self::e($title) . '</h2>' : '',
            'text' => self::text($data['text'] ?? ''),
            'groups' => self::groups($data['groups'] ?? []),
            'icons' => ($data['icons'] ?? true) === false ? '' : self::icons($data),
            'buttons' => self::buttons($data['buttons'] ?? []),
        ];
        if (trim(implode('', $html)) === '') return '';

        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', $html + [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
        ]);
    }

    private static function text(mixed $text): string
    {
        $html = '';
        foreach ((array) $text as $p) {
            $p = is_scalar($p) ? trim(strip_tags((string) $p, self::ALLOWED_TAGS)) : '';
            if ($p !== '') $html .= '<p class="knowledge-listing-text">' . $p . '</p>';
        }
        return $html;
    }

    private static function groups(mixed $groups): string
    {
        if (!is_array($groups)) return '';
        $html = '';
        foreach ($groups as $group) {
            if (!is_array($group)) continue;
            $items = '';
            foreach ((array) ($group['list'] ?? $group['text_list'] ?? []) as $item) {
                if (is_scalar($item) && trim((string) $item) !== '') $items .= '<li>' . self::e(trim((string) $item)) . '</li>';
            }
            $title = self::str($group['title'] ?? '');
            if ($items === '' && $title === '') continue;
            $html .= '<div class="knowledge-group">'
                . ($title !== '' ? '<p class="knowledge-group-title">' . self::e($title) . '</p>' : '')
                . ($items !== '' ? '<ul>' . $items . '</ul>' : '') . '</div>';
        }
        return $html === '' ? '' : '<div class="knowledge-groups">' . $html . '</div>';
    }

    private static function icons(array $data): string
    {
        $type = self::str($data['icons_type'] ?? '') ?: 'knowledge_item';
        $items = PlatformDataService::get_all_items_from_file($type) ?? [];
        if (!is_array($items)) return '';

        $html = '';
        foreach ($items as $item) {
            if (!is_array($item) || ($item['settings']['render'] ?? true) === false) continue;
            $icon = self::str($item['svg'] ?? '');
            if ($icon === '') continue;
            $text = self::str($item['text'] ?? '');
            $svg = (string) PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => 'knowledge-icon']);
            if (trim($svg) === '') continue;

            $url = self::str($item['external_url'] ?? '');
            $label = $text !== '' ? ' title="' . self::e($text) . '" aria-label="' . self::e($text) . '"' : '';
            $html .= $url !== ''
                ? '<li><a href="' . self::e($url) . '" target="_blank" rel="noopener"' . $label . '>' . $svg . '</a></li>'
                : '<li' . $label . '>' . $svg . '</li>';
        }
        if ($html === '') return '';

        $iconsTitle = self::str($data['icons_title'] ?? '');
        return ($iconsTitle !== '' ? '<p class="knowledge-group-title">' . self::e($iconsTitle) . '</p>' : '')
            . '<ul class="knowledge-icons">' . $html . '</ul>';
    }

    private static function buttons(mixed $buttons): string
    {
        if (!is_array($buttons)) return '';
        $html = '';
        foreach ($buttons as $btn) {
            if (!is_array($btn)) continue;
            $id = self::str($btn['_id'] ?? '');
            $html .= (string) PlatformComponentRenderer::render('button', [
                'text' => self::str($btn['text'] ?? ''),
                'link' => $id !== '' ? ['post_id' => $id] : ($btn['link'] ?? ''),
                'svg' => self::str($btn['svg'] ?? ''),
                'class' => self::str($btn['class'] ?? '') ?: 'btn btn-primary',
            ]);
        }
        return $html === '' ? '' : '<div class="knowledge-listing-buttons">' . $html . '</div>';
    }

    private static function str(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
