<?php

class SectionBlogHeader
{
    public static function render(array $data = []): string
    {
        $blogData = $data['blogData'] ?? (DataService::get_personal_data()['posts_socials'] ?? []);
        $urlPath = DataService::get_url_path();

        $colors = $blogData['blog_colors'] ?? [];
        $style = '';
        if (!empty($colors['blog_color_primary']) && !empty($colors['blog_color_secondary'])) {
            $style = ' style="'
                . '--blog-color-primary: ' . htmlspecialchars($colors['blog_color_primary']) . ';'
                . '--blog-color-secondary: ' . htmlspecialchars($colors['blog_color_secondary']) . ';'
                . '"';
        }

        $wallpaperHtml = '';
        if (!empty($blogData['blog-img-wallpaper'])) {
            $wallpaperHtml .= render_image($urlPath . $blogData['blog-img-wallpaper']);
        }

        $logoHtml = '';
        if (!empty($blogData['blog_img_logo'])) {
            $logoHtml .= render_image($urlPath . $blogData['blog_img_logo']);
        }

        $lightsHtml = '';
        for ($i = 1; $i <= 9; $i++) {
            $lightsHtml .= '<div class="light x' . $i . '"></div>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ style }}', '{{ wallpaper_html }}', '{{ logo_html }}', '{{ username }}', '{{ description }}', '{{ lights_html }}'],
            [$style, $wallpaperHtml, $logoHtml, htmlspecialchars($blogData['blog_username'] ?? ''), htmlspecialchars($blogData['blog-description'] ?? ''), $lightsHtml],
            $template
        );
    }
}
