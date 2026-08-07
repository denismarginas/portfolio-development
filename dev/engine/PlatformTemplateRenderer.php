<?php

class PlatformTemplateRenderer
{
    public static function render(string $templatePath, array $data): string
    {
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }

        return $html;
    }
}
