<?php

class HeaderLogo
{
    public static function render(array $data = []): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);

        foreach ([
            'front_page_slug' => $data['frontPageSlug'] ?? '',
            'logo_path' => $data['logoPath'] ?? '',
            'site_identity' => $data['siteIdentity'] ?? '',
            'primary_title' => $data['primaryTitle'] ?? '',
            'secondary_title' => $data['secondaryTitle'] ?? '',
        ] as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }

        return $html;
    }
}
