<?php

class Section404
{
    public static function render(array $data = []): string
    {
        $notFound = $data['notFound'] ?? (DataService::get_personal_data()['page-not-found'] ?? []);

        $title = htmlspecialchars($notFound['title'] ?? '404');
        $description = htmlspecialchars($notFound['description'] ?? 'NOT FOUND');

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ title }}', '{{ description }}'],
            [$title, $description],
            $template
        );
    }
}
