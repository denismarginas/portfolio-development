<?php

class Svg
{
    public function render(array $data = []): string
    {
        $icon = $data['icon'] ?? '';
        $class = $data['class'] ?? 'svg-icon';

        if (empty($icon)) {
            return '';
        }

        $svgClass = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        $svgIcon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ svg_class }}', '{{ svg_icon }}'],
            [$svgClass, $svgIcon],
            $template
        );
    }
}
