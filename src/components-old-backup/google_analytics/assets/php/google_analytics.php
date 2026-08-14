<?php

class GoogleAnalytics
{
    public static function render(array $data): string
    {
        $googleAnalyticsId = $data['googleAnalyticsId'] ?? null;

        if (empty($googleAnalyticsId)) {
            return '';
        }

        $id = htmlspecialchars($googleAnalyticsId);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ analytics_id }}', $id, $template);
    }
}
