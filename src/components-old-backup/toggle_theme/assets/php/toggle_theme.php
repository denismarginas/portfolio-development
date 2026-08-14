<?php

class ToggleTheme
{
    public static function render(array $data = []): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        $html = str_replace('{{ svg_sun }}', svg_get('sun'), $html);
        return $html;
    }
}
