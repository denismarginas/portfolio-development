<?php

class ParagraphBlock
{
    public static function render(array $data = []): string
    {
        $text = $data['text'] ?? '';
        $children = $data['children'] ?? [];
        $style = $data['style'] ?? '';

        $childrenHtml = '';
        foreach ($children as $child) {
            $childName = $child['component'] ?? '';
            if (!$childName) continue;
            $childData = array_merge($child['data'] ?? [], [
                'post_current_data' => $data['post_current_data'] ?? [],
                'global_content_path' => $data['global_content_path'] ?? '',
                'global_img_path' => $data['global_img_path'] ?? '',
                'global_vid_path' => $data['global_vid_path'] ?? '',
                'children' => $child['children'] ?? [],
            ]);
            $childrenHtml .= ComponentRenderer::render_component(str_replace('-', '_', $childName), $childData);
        }

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
