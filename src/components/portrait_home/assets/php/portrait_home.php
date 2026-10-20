<?php

/**
 *   {
 *     "component": "portrait_home",
 *     "data": {
 *       "img": "personal-images/dm-personal-image-2.webp",
 *       "alt": "Denis Marginas",
 *       "max_width": "440px",
 *       "chips": [
 *         { "svg": "wordpress", "text": "WordPress" },
 *         { "svg": "php", "text": "PHP" }
 *       ]
 *     }
 *   }
 *
 * A cut-out photo (transparent background) standing in a tall arch, the head breaking out of its top,
 * with up to 6 floating chips around it (different from portrait_experience / portrait_about / the contact circle).
 *
 * data:
 *   img        string  image path (relative to src/content/img/ or a full "src/..." path), best a transparent cut-out
 *   alt        string  alt text (default "Portrait")
 *   chips      array   up to 6 { svg, text } floating badges (positions chip-1 ... chip-6), optional
 *   parallax   bool    subtle scroll movement of the chips / photo + fade-in when it enters the view (default true)
 *   max_width  string  optional CSS width limit, e.g. "440px" (default 100% of the column)
 *   class, id  string  optional
 */
class portrait_home
{
    private const MAX_CHIPS = 6;

    public static function render(array $data = []): string
    {
        $src = is_scalar($data['img'] ?? null) ? trim((string) $data['img']) : '';
        if ($src === '') return '';

        $image = (string) PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => is_scalar($data['alt'] ?? null) ? (string) $data['alt'] : 'Portrait',
            'class' => 'portrait-home-img',
            'lazy' => true,
        ]);
        if (trim($image) === '') return '';

        $chips = '';
        $nr = 0;
        foreach ((array) ($data['chips'] ?? []) as $chip) {
            if ($nr >= self::MAX_CHIPS) break;
            if (is_string($chip)) $chip = ['text' => $chip];
            if (!is_array($chip)) continue;
            $text = trim((string) ($chip['text'] ?? ''));
            $svg = trim((string) ($chip['svg'] ?? ''));
            if ($text === '' && $svg === '') continue;
            $nr++;
            $icon = $svg !== '' ? (string) PlatformComponentRenderer::render('svg', ['icon' => $svg, 'class' => 'portrait-home-chip-icon']) : '';
            $chips .= '<span class="portrait-home-chip chip-' . $nr . '">' . $icon
                . ($text !== '' ? '<span class="portrait-home-chip-text">' . self::e($text) . '</span>' : '') . '</span>';
        }

        $class = is_scalar($data['class'] ?? null) ? trim((string) $data['class']) : '';
        $id = is_scalar($data['id'] ?? null) ? trim((string) $data['id']) : '';
        $maxWidth = is_scalar($data['max_width'] ?? null) ? trim((string) $data['max_width']) : '';
        $style = $maxWidth !== '' && !preg_match('/[;{}<>"\']/', $maxWidth) ? ' style="max-width: ' . self::e($maxWidth) . ';"' : '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'parallax_attr' => ($data['parallax'] ?? true) !== false ? ' data-portrait-home-parallax' : '',
            'id_attr' => ($id !== '' ? ' id="' . self::e($id) . '"' : '') . $style,
            'image' => $image,
            'chips' => $chips !== '' ? '<div class="portrait-home-chips" aria-hidden="true">' . $chips . '</div>' : '',
        ]);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
