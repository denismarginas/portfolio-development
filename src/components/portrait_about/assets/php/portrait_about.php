<?php

/**
 *   { "component": "portrait_about", "data": { "img": "personal-images/dm-personal-image-1.webp", "alt": "Denis Marginas" } }
 *
 * data:
 *   img        string  image path (relative to src/content/img/ or a full "src/..." path)
 *   alt        string  alt text (default "Portrait")
 *   graphic    string  svg icon behind the photo (default "hexagon-3d"; "" = none)
 *   max_width  string  optional CSS width limit, e.g. "420px" (default 100% of the column)
 *   class, id  string  optional
 */
class portrait_about
{
    public static function render(array $data = []): string
    {
        $src = self::str($data['img'] ?? '');
        if ($src === '') return '';

        $image = trim((string) PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => self::str($data['alt'] ?? '') ?: 'Portrait',
            'class' => 'portrait-about-img',
            'lazy' => true,
        ]));
        if ($image === '') return '';

        $icon = array_key_exists('graphic', $data) ? self::str($data['graphic']) : 'hexagon-3d';
        $graphic = '';
        if ($icon !== '') {
            $svg = trim((string) PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => 'portrait-about-svg']));
            if ($svg !== '') $graphic = '<div class="portrait-about-graphic" aria-hidden="true">' . $svg . '</div>';
        }

        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');
        $maxWidth = self::str($data['max_width'] ?? '');
        $style = $maxWidth !== '' && !preg_match('/[;{}<>"\']/', $maxWidth) ? ' style="--portrait-about-max-width: ' . self::e($maxWidth) . ';"' : '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => ($id !== '' ? ' id="' . self::e($id) . '"' : '') . $style,
            'image' => $image,
            'graphic' => $graphic,
        ]);
    }

    private static function str(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
