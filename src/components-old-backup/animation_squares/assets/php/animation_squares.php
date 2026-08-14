<?php

class AnimationSquares
{
    public static function render(array $data): string
    {
        $extraClass = $data['class'] ?? 'grid-background-animation';
        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ extra_class }}', htmlspecialchars($extraClass), $template);
    }
}
