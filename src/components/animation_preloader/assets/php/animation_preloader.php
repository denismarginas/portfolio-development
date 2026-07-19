<?php

class AnimationPreloader
{
    public static function render(array $data): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }
        return file_get_contents($templatePath);
    }
}
