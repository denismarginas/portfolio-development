<?php

/**
 *   {
 *     "component": "block_tags",
 *     "data": {
 *       "tags": [
 *         { "text": "photo", "link": "#photo" },
 *         { "text": "video", "link": "#video" }
 *       ]
 *     }
 *   }
 *
 * data:
 *   tags   array   { "text", "link"? } objects or plain strings ("photo" -> "#photo", no link).
 *                  A leading "#" in the text is ignored (it is always added).
 *                  Also accepted as JSON text or a comma separated string (editor friendly).
 *   class  string  optional extra class
 *   id     string  optional id
 *
 * Renders nothing when there are no tags.
 */
class block_tags
{
    private const HTML_DIR = __DIR__ . '/../html/';

    public static function render(array $data = []): string
    {
        $html = '';
        foreach (self::list($data['tags'] ?? []) as $tag) {
            $text = ltrim(trim(is_array($tag) ? (string) ($tag['text'] ?? '') : (string) $tag), '#');
            if ($text === '') continue;

            $link = is_array($tag) ? trim((string) ($tag['link'] ?? $tag['url'] ?? '')) : '';
            if (preg_match('/^\s*(javascript|data|vbscript):/i', $link)) $link = '';

            $html .= PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/' . ($link !== '' ? 'tag_link.html' : 'tag.html'), [
                'text' => self::esc($text),
                'link' => self::esc($link),
            ]);
        }
        if ($html === '') return '';

        $extra = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'extra_class' => $extra !== '' ? ' ' . self::esc($extra) : '',
            'id_attr' => $id !== '' ? ' id="' . self::esc($id) . '"' : '',
            'tags' => $html,
        ]);
    }

    private static function list(mixed $tags): array
    {
        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            $tags = is_array($decoded) ? $decoded : explode(',', $tags);
        }
        return is_array($tags) ? $tags : [];
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
