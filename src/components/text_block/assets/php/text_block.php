<?php

/**
 * Generic renderer for structured text content.
 *
 * Usage:
 *   PlatformComponentRenderer::render('text_block', ['elements' => [
 *       ['title' => 'Section title'],                                // h2.title-divider.title-primary (title component)
 *       ['title' => ['text' => 'Smaller', 'tag' => 'h3', 'class' => 'title-divider title-secondary']],
 *       ['subtitle' => 'Sub heading'],                                // h3 (string or the same object as title)
 *       ['paragraphs' => ['First paragraph.', 'Second paragraph.']], // or 'paragraph'/'text'/'texts', string or array
 *       ['list' => ['Item one', 'Item two']],                        // simple (scalar-only) array -> <ul><li>
 *       ['buttons' => [['text' => '...', 'link' => '...', 'svg' => '...']]], // or 'btns', wrapped in a div
 *       ['button' => ['text' => '...', 'link' => '...', 'svg' => '...']],    // or 'btn', not wrapped
 *   ]]);
 *
 * Each element may also carry:
 *   'class'  -> extra class appended to the produced tag (p/ul/subtitle heading/buttons wrapper)
 *   title / subtitle as object: { "text", "tag" (h1-h6), "class" }
 *     title defaults:    h2, "title-divider title-primary"
 *     subtitle defaults: h3, the element 'class'
 *   (old flat 'title_tag' / 'title_class' / 'subtitle_tag' / 'subtitle_class' still work)
 *   'escape' -> set to false to output paragraph text raw (already-safe/rich text), defaults to true
 *
 * Elements render in the order given, so callers control ordering (e.g. title, then list, then paragraph again).
 *
 * Variables: "{{name}}" placeholders in any element text are replaced from data.vars, before escaping.
 * A var is a plain value, or a utility component whose value() result is used:
 *   "vars": {
 *     "age": { "component": "utility_age", "data": { "date": "09-09-1997" } },
 *     "city": "Baia Mare"
 *   },
 *   "elements": [ { "paragraphs": ["I'm {{age}} years old and I live in {{city}}."] } ]
 * Unknown placeholders are left as they are.
 */
class text_block
{
    public static function render(array $data = []): string
    {
        $elements = $data['elements'] ?? [];
        if (!is_array($elements) || empty($elements)) return '';

        $vars = self::resolve_vars($data['vars'] ?? []);
        if (!empty($vars)) $elements = self::fill_vars($elements, $vars);

        $html = '';
        foreach ($elements as $element) {
            if (is_array($element)) $html .= self::render_element($element);
        }
        return $html;
    }

    /** data.vars -> [ name => string ] (component vars resolved through their value()). */
    private static function resolve_vars(mixed $vars): array
    {
        if (!is_array($vars)) return [];
        $out = [];
        foreach ($vars as $name => $var) {
            $name = trim((string) $name);
            if ($name === '') continue;

            if (is_array($var) && isset($var['component'])) {
                $component = str_replace('-', '_', trim((string) $var['component']));
                $value = $component !== ''
                    ? PlatformComponentRenderer::value($component, is_array($var['data'] ?? null) ? $var['data'] : [])
                    : null;
            } else {
                $value = $var;
            }

            if (is_scalar($value)) $out[$name] = (string) $value;
        }
        return $out;
    }

    /** Replaces {{name}} in every string of the elements (recursively). */
    private static function fill_vars(mixed $value, array $vars): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) $value[$k] = self::fill_vars($v, $vars);
            return $value;
        }
        if (!is_string($value) || !str_contains($value, '{{')) return $value;

        return preg_replace_callback(
            '/\{\{\s*([A-Za-z0-9_\-]+)\s*\}\}/',
            fn ($m) => array_key_exists($m[1], $vars) ? $vars[$m[1]] : $m[0],
            $value
        ) ?? $value;
    }

    private static function render_element(array $element): string
    {
        $html = '';

        if (isset($element['title'])) {
            $html .= self::render_heading($element['title'], [
                'tag' => $element['title_tag'] ?? 'h2',
                'class' => $element['title_class'] ?? null,
            ]);
        }

        if (isset($element['subtitle'])) {
            $html .= self::render_heading($element['subtitle'], [
                'tag' => $element['subtitle_tag'] ?? 'h3',
                'class' => $element['subtitle_class'] ?? (string) ($element['class'] ?? ''),
            ]);
        }

        $text = $element['paragraphs'] ?? $element['paragraph'] ?? $element['texts'] ?? $element['text'] ?? null;
        if ($text !== null) {
            $escape = !array_key_exists('escape', $element) || $element['escape'] !== false;
            $html .= self::render_paragraphs($text, (string) ($element['class'] ?? ''), $escape);
        }

        if (isset($element['list']) && is_array($element['list'])) {
            $html .= self::render_list($element['list'], (string) ($element['class'] ?? ''));
        }

        $buttons = $element['buttons'] ?? $element['btns'] ?? null;
        if (is_array($buttons)) {
            $html .= self::render_buttons($buttons, true, (string) ($element['class'] ?? ''));
        }

        $button = $element['button'] ?? $element['btn'] ?? null;
        if (is_array($button)) {
            $html .= self::render_buttons([$button], false);
        }

        return $html;
    }

    /**
     * Heading through the "title" component.
     *   $value     string | { text, tag, class, id }
     *   $defaults  [ 'tag' => …, 'class' => … ] used when the object doesn't set them (class null = title default)
     */
    private static function render_heading(mixed $value, array $defaults): string
    {
        $data = title::normalize($value);
        if (!array_key_exists('tag', $data)) $data['tag'] = $defaults['tag'] ?? 'h2';
        if (!array_key_exists('class', $data)) $data['class'] = $defaults['class'] ?? null;
        return (string) PlatformComponentRenderer::render('title', $data);
    }

    private static function render_paragraphs(mixed $text, string $class, bool $escape): string
    {
        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';

        if (is_array($text)) {
            $html = '';
            foreach ($text as $p) {
                $p = trim((string) $p);
                if ($p === '') continue;
                $html .= '<p' . $classAttr . '>' . ($escape ? htmlspecialchars($p, ENT_QUOTES, 'UTF-8') : $p) . '</p>';
            }
            return $html;
        }

        $text = trim((string) $text);
        if ($text === '') return '';
        return '<p' . $classAttr . '>' . ($escape ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8') : $text) . '</p>';
    }

    private static function render_list(array $list, string $class): string
    {
        if (empty($list)) return '';
        foreach ($list as $item) {
            if (is_array($item)) return '';
        }

        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $html = '<ul' . $classAttr . '>';
        foreach ($list as $item) {
            $html .= '<li>' . htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    private static function render_buttons(array $list, bool $wrap, string $class = ''): string
    {
        $html = '';
        foreach ($list as $btn) {
            if (!is_array($btn)) continue;
            $html .= PlatformComponentRenderer::render('button', [
                'text' => (string) ($btn['text'] ?? ''),
                'link' => $btn['link'] ?? $btn['_id'] ?? '',
                'svg' => (string) ($btn['svg'] ?? ''),
                'class' => (string) ($btn['class'] ?? 'btn btn-primary'),
            ]);
        }
        if ($html === '') return '';
        if (!$wrap) return $html;

        $wrapClass = $class !== '' ? $class : 'actions';
        return '<div class="' . htmlspecialchars($wrapClass, ENT_QUOTES, 'UTF-8') . '">' . $html . '</div>';
    }
}
