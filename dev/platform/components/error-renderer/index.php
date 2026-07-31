<?php

class error_renderer
{
    private const STYLE = 'color:#c3122e;border:1px solid #c3122e;background:rgba(228, 209, 167, 0.3);padding:14px 8px;border-radius:4px;font-size:12px;margin:10px;text-align:center;';

    private const FALLBACKS = [
        'component_not_found' => 'Component not found: {name}',
        'no_php_files'        => 'No PHP files for component: {name}',
        'class_not_found'     => 'Component class not found: {class}',
        'class_not_loaded'    => 'Component class not loaded: {class}',
        'no_render_method'    => 'Component has no render method: {class}',
    ];

    public static function render(string $key, array $params = []): string
    {
        $message = platform_data::getString('errors.' . $key, self::FALLBACKS[$key] ?? 'Error: ' . $key);

        foreach ($params as $name => $value) {
            $message = str_replace('{' . $name . '}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $message);
        }

        return '<div style="' . self::STYLE . '">' . $message . '</div>';
    }
}
