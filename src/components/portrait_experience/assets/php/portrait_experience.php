<?php

/**
 *   { "component": "portrait_experience", "data": { "img": "personal-images/dm-personal-with-laptop.webp" } }
 *
 * data:
 *   img        string  image path (relative to src/content/img/ or a full "src/..." path)
 *   alt        string  alt text (default "Portrait")
 *   graphic    string  svg icon behind the photo (default "dots-graphic"; "" = none)
 *   max_width  string  optional CSS width limit, e.g. "420px" (default 100% of the column)
 *   class, id  string  optional
 */
class portrait_experience
{
    public static function render(array $data = []): string
    {
        $src = is_scalar($data['img'] ?? null) ? trim((string) $data['img']) : '';
        if ($src === '') return '';

        $image = (string) PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => is_scalar($data['alt'] ?? null) ? (string) $data['alt'] : 'Portrait',
            'class' => 'portrait-experience-img',
            'lazy' => true,
        ]);
        if (trim($image) === '') return '';

        $icon = array_key_exists('graphic', $data) ? (is_scalar($data['graphic']) ? trim((string) $data['graphic']) : '') : 'dots-graphic';
        $graphic = $icon !== ''
            ? '<div class="portrait-experience-graphic" aria-hidden="true">' . PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => 'portrait-experience-dots']) . '</div>'
            : '';

        $class = is_scalar($data['class'] ?? null) ? trim((string) $data['class']) : '';
        $id = is_scalar($data['id'] ?? null) ? trim((string) $data['id']) : '';
        $maxWidth = is_scalar($data['max_width'] ?? null) ? trim((string) $data['max_width']) : '';
        $style = $maxWidth !== '' && !preg_match('/[;{}<>"\']/', $maxWidth) ? ' style="max-width: ' . htmlspecialchars($maxWidth, ENT_QUOTES, 'UTF-8') . ';"' : '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') : '',
            'id_attr' => ($id !== '' ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : '') . $style,
            'image' => $image,
            'graphic' => $graphic,
        ]);
    }
}
