<?php

/**
 *   {
 *     "component": "projects_previews",
 *     "data": {
 *       "screen": { "img": "design-elements/monitor-with-project.webp", "alt": "Monitor with a project" },
 *       "cards": [
 *         { "img": "projects/agromir/media-3d-tire-animation/3d-editing-blender-agromir-1.webp", "alt": "3D editing" },
 *         { "img": "projects/agromir/media-video-editing/video-editing-agromir-1.webp" }
 *       ]
 *     }
 *   }
 *
 * data:
 *   screen     object  { img, alt } monitor image shown on the right (optional)
 *   cards      array   [{ img, alt }] project screenshots, max 5 (each has its own 3D position: card-1 … card-5)
 *   glow       bool    soft primary-color glow behind the cards (default true, shown from 2 cards up)
 *   class, id  string  optional
 *
 * img: path relative to src/content/img/ (or a full "src/..." path). Missing images are skipped.
 * Purely decorative (aria-hidden), so put the meaningful text next to it (e.g. a text_block in a column).
 */
class projects_previews
{
    private const MAX_CARDS = 5;

    public static function render(array $data = []): string
    {
        $cards = self::cards($data['cards'] ?? []);
        $screen = self::screen($data['screen'] ?? []);
        if ($cards === '' && $screen === '') return '';

        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'cards' => $cards === '' ? '' : '<div class="projects-previews-cards">'
                . $cards
                . (($data['glow'] ?? true) !== false && substr_count($cards, '<img') > 1 ? '<span class="projects-previews-glow"></span>' : '')
                . '</div>',
            'screen' => $screen,
        ]);
    }

    private static function cards(mixed $cards): string
    {
        if (!is_array($cards)) return '';
        $html = '';
        $nr = 0;
        foreach ($cards as $card) {
            if ($nr >= self::MAX_CARDS) break;
            $img = self::img($card, 'projects-previews-card card-' . ($nr + 1));
            if ($img === '') continue;
            $html .= $img;
            $nr++;
        }
        return $html;
    }

    private static function screen(mixed $screen): string
    {
        $img = self::img($screen, 'projects-previews-screen-img');
        return $img === '' ? '' : '<div class="projects-previews-screen">' . $img . '</div>';
    }

    private static function img(mixed $item, string $class): string
    {
        if (is_string($item)) $item = ['img' => $item];
        if (!is_array($item)) return '';
        $src = self::str($item['img'] ?? $item['src'] ?? '');
        if ($src === '') return '';

        return trim((string) PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => self::str($item['alt'] ?? ''),
            'class' => $class,
            'lazy' => true,
        ]));
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
