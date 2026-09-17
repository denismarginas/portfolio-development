<?php

/**
 * Generic renderer for structured text content.
 *
 * Usage:
 *   PlatformComponentRenderer::render('text_block', ['elements' => [
 *       ['title' => 'Section title'],
 *       ['subtitle' => 'Sub heading'],
 *       ['paragraphs' => ['First paragraph.', 'Second paragraph.']], // or 'paragraph'/'text'/'texts', string or array
 *       ['list' => ['Item one', 'Item two']],                        // simple (scalar-only) array -> <ul><li>
 *       ['buttons' => [['text' => '...', 'link' => '...', 'svg' => '...']]], // or 'btns', wrapped in a div
 *       ['button' => ['text' => '...', 'link' => '...', 'svg' => '...']],    // or 'btn', not wrapped
 *   ]]);
 *
 * Each element may also carry:
 *   'class'  -> extra class appended to the produced tag (p/ul/subtitle heading/buttons wrapper)
 *   'escape' -> set to false to output paragraph text raw (already-safe/rich text), defaults to true
 *
 * Elements render in the order given, so callers control ordering (e.g. title, then list, then paragraph again).
 */
class text_block
{
    public static function render(array $data = []): string
    {
        $elements = $data['elements'] ?? [];
        if (!is_array($elements) || empty($elements)) return '';

        $html = '';
        foreach ($elements as $element) {
            if (is_array($element)) $html .= self::render_element($element);
        }
        return $html;
    }

    private static function render_element(array $element): string
    {
        $html = '';

        if (isset($element['title'])) {
            $html .= self::render_heading((string) $element['title'], 'h2', 'title-divider');
        }

        if (isset($element['subtitle'])) {
            $html .= self::render_heading((string) $element['subtitle'], 'h3', (string) ($element['class'] ?? ''));
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

    private static function render_heading(string $text, string $tag, string $class = ''): string
    {
        $text = trim($text);
        if ($text === '') return '';
        $classAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        return '<' . $tag . $classAttr . '>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</' . $tag . '>';
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
