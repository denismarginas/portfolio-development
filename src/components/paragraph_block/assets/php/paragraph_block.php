<?php

class ParagraphBlock
{
    public static function render(array $data = []): string
    {
        $text = $data['text'] ?? '';
        $childrenHtml = $data['children_html'] ?? '';
        $style = $data['style'] ?? '';

        $styleAttr = '';
        if (!empty($style)) {
            $styleAttr = ' style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '"';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ style_attr }}', '{{ text }}', '{{ children_html }}'],
            [$styleAttr, htmlspecialchars($text, ENT_QUOTES, 'UTF-8'), $childrenHtml],
            $template
        );
    }
}
