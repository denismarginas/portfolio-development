<?php

class Title
{
    public static function render(array $data = []): string
    {
        $text = $data['text'] ?? $data['title'] ?? 'Title';
        $id = Helpers::remove_space_and_lowercase($text);
        $class = $data['class'] ?? 'dm-post-title-category';

        $titleId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $titleClass = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        $titleText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ title_id }}', '{{ title_class }}', '{{ title_text }}'],
            [$titleId, $titleClass, $titleText],
            $template
        );
    }
}
