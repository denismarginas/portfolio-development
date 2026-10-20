<?php

/**
 * Heading with the theme title classes.
 *
 *   { "component": "title", "data": { "text": "About Me" } }
 *     -> <h2 class="title-divider title-primary">About Me</h2>
 *   { "component": "title", "data": { "text": "Resumes", "tag": "h3", "class": "title-divider title-secondary" } }
 *
 * Inside other components the title is one "title" value, a string or an object:
 *   "title": "Projects"
 *   "title": { "text": "Projects", "tag": "h2", "class": "title-divider title-primary font-secondary" }
 * (old flat "title_tag" / "title_class" next to a string title still work)
 *
 * From PHP (other components):
 *   PlatformComponentRenderer::render('title', title::normalize($data['title'] ?? null, $data, ['base_class' => 'profile-card-title']));
 *
 * data:
 *   text        string  heading text (required; "content" works too)
 *   tag         string  "h1" | "h2" (default) | "h3" | "h4" | "h5" | "h6"
 *   class       string  title classes (default "title-divider title-primary"; "" = none). Theme classes:
 *                         title-primary    big responsive size (4xl -> 3xl -> xl -> lg)
 *                         title-secondary  smaller responsive size (3xl -> 2xl -> lg -> base)
 *                         title-tertiary   small section size (lg -> base -> md)
 *                         title-divider    short primary-colored line under the text
 *   base_class  string  fixed component class kept in front of "class" (for components, e.g. "profile-card-title")
 *   id          string  optional
 */
class title
{
    public const DEFAULT_CLASS = 'title-divider title-primary';
    private const TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    public static function render(array $data = []): string
    {
        $raw = $data['text'] ?? $data['content'] ?? null;
        $text = is_scalar($raw) ? trim((string) $raw) : '';
        if ($text === '') return '';

        $tag = is_scalar($data['tag'] ?? null) ? strtolower(trim((string) $data['tag'])) : '';
        if (!in_array($tag, self::TAGS, true)) $tag = 'h2';

        $class = array_key_exists('class', $data) && $data['class'] !== null
            ? (is_scalar($data['class']) ? trim((string) $data['class']) : self::DEFAULT_CLASS)
            : self::DEFAULT_CLASS;
        $base = is_scalar($data['base_class'] ?? null) ? trim((string) $data['base_class']) : '';
        $classes = trim($base . ' ' . $class);

        $id = is_scalar($data['id'] ?? null) ? trim((string) $data['id']) : '';

        return '<' . $tag
            . ($classes !== '' ? ' class="' . self::e(preg_replace('/\s+/', ' ', $classes)) . '"' : '')
            . ($id !== '' ? ' id="' . self::e($id) . '"' : '')
            . '>' . self::e($text) . '</' . $tag . '>';
    }

    /**
     * A component's "title" value -> data for render().
     *   $title   string ("Projects") | object { text|content, tag, class, id } | null
     *   $legacy  the component's data: its old flat "title_tag" / "title_class" are used when the
     *            object doesn't set tag / class
     *   $extra   fixed keys added by the component (e.g. [ 'base_class' => 'profile-card-title' ])
     * A missing "class" keeps render()'s default ("title-divider title-primary").
     */
    public static function normalize(mixed $title, array $legacy = [], array $extra = []): array
    {
        $out = is_array($title)
            ? array_intersect_key($title, array_flip(['text', 'content', 'tag', 'class', 'id']))
            : ['text' => is_scalar($title) ? (string) $title : ''];

        if (!array_key_exists('tag', $out) && isset($legacy['title_tag'])) $out['tag'] = $legacy['title_tag'];
        if (!array_key_exists('class', $out) && array_key_exists('title_class', $legacy)) $out['class'] = $legacy['title_class'];

        return array_merge($out, $extra);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
