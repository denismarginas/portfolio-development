<?php

class CookieNotice
{
    public static function render(array $data): string
    {
        $cookieNotice = $data['cookieNotice'] ?? [];

        if (($cookieNotice['display'] ?? null) !== 'true') {
            return '';
        }

        $descriptionHtml = '';
        if (!empty($cookieNotice['description_text'])) {
            $descriptionHtml .= '<p class="text">';
            $descriptionHtml .= htmlspecialchars($cookieNotice['description_text']);

            $pageLink = '';
            if (!empty($cookieNotice['page']['title']) && !empty($cookieNotice['page']['slug'])) {
                $pageLink = '<a class="link" href="' . htmlspecialchars($cookieNotice['page']['slug']) . '" target="_blank">' . htmlspecialchars($cookieNotice['page']['title']) . '</a>';
            }

            if (!empty($cookieNotice['page_text'])) {
                $descriptionHtml .= sprintf($cookieNotice['page_text'], $pageLink);
            } elseif (!empty($pageLink)) {
                $descriptionHtml .= $pageLink;
            }

            $descriptionHtml .= '</p>';
        }

        $buttonHtml = '';
        if (!empty($cookieNotice['button_text'])) {
            $buttonHtml .= '<button class="button-accept" data-button="primary" data-toggle="collapse" aria-controls="cookie_notice" aria-expanded="true">';
            if (class_exists('SVG')) {
                $buttonHtml .= svg_get('cookie');
            }
            $buttonHtml .= htmlspecialchars($cookieNotice['button_text']);
            $buttonHtml .= '</button>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(['{{ description_html }}', '{{ button_html }}'], [$descriptionHtml, $buttonHtml], $template);
    }
}
