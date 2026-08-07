<?php

trait header_logo
{
    protected static function render_logo(array $settings): string
    {
        $siteTitle = (string) ($settings['site_title'] ?? '');
        $logoImg = (string) ($settings['logo']['site_logo_img'] ?? '');

        $primary = $siteTitle;
        $secondary = '';
        $spacePos = strpos($siteTitle, ' ');
        if ($spacePos !== false) {
            $primary = trim(substr($siteTitle, 0, $spacePos));
            $secondary = trim(substr($siteTitle, $spacePos));
        }

        $imgHtml = '';
        $resolvedImg = self::resolve_logo_img($logoImg);
        if ($resolvedImg !== '') {
            $imgInner = PlatformComponentRenderer::render('image', [
                'src' => $resolvedImg,
                'alt' => $siteTitle !== '' ? $siteTitle . ' Logo' : '',
                'class' => 'logo-img',
                'width' => "50",
                'height' => "50",
            ]);
            $imgHtml = PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_logo_img.html', [
                'img_html' => $imgInner,
            ]);
        }

        $textHtml = '';
        if ($siteTitle !== '') {
            $primaryHtml = PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_logo_text_primary.html', [
                'primary_text' => htmlspecialchars($primary, ENT_QUOTES, 'UTF-8'),
            ]);

            $secondaryHtml = '';
            if ($secondary !== '') {
                $secondaryHtml = PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_logo_text_secondary.html', [
                    'secondary_text' => htmlspecialchars($secondary, ENT_QUOTES, 'UTF-8'),
                ]);
            }

            $textHtml = PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_logo_text.html', [
                'template_logo_texts' => $primaryHtml . $secondaryHtml,
            ]);
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_logo.html', [
            'logo_url' => htmlspecialchars(PlatformPathService::front_page_link(), ENT_QUOTES, 'UTF-8'),
            'img_html' => $imgHtml,
            'text_html' => $textHtml,
        ]);
    }

    protected static function resolve_logo_img(string $logoImg): string
    {
        if ($logoImg === '') {
            return '';
        }
        $raw = ltrim($logoImg, '/');
        if (PlatformUrlService::is_external_url($raw)) {
            return $logoImg;
        }

        $candidates = [];
        if (strpos($raw, 'src/content/') !== 0) {
            $candidates[] = 'src/content/img/' . $raw;
        }
        $candidates[] = $raw;

        foreach ($candidates as $candidate) {
            $absolute = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT . '/' . $candidate : $candidate;
            if (file_exists($absolute)) {
                return $candidate;
            }
        }

        return '';
    }
}
