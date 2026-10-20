<?php

/**
 *   {
 *     "component": "hero_home",
 *     "data": {
 *       "eyebrow": "Full-Stack Web Developer",
 *       "title": "Denis Mărginaș",
 *       "text": "I build websites, online shops and web apps ...",
 *       "buttons": [ { "text": "View Projects", "svg": "projects", "link": { "post_id": "experience" }, "class": "btn btn-primary" } ],
 *       "video": { "video_id": "denismarginas-experience-highlights", "start": 0, "end": "auto", "loop": true, "audio": false, "overlay": 0.65 },
 *       "scroll": true
 *     },
 *     "children": [ { "component": "overlay_noise", "data": {} } ]
 *   }
 *
 * data:
 *   eyebrow    string  small line above the title (optional)
 *   title      string  h1; "*word*" is highlighted with the gradient (e.g. "Hi, I'm *Denis*")
 *   text       string|array  intro paragraph(s); <br>, <b>, <strong>, <i>, <em> are kept
 *   buttons    array   button component data { text, svg, link (url or { post_id }), class (default "btn btn-outline") }
 *   video      object  video_background data (video_id or src, start, end / duration, loop, audio, overlay, poster ...)
 *   align      string  "center" (default) | "left"
 *   height     string  "full" (default, ~viewport) | "lg" | "md"
 *   scroll     bool|object  scroll-down cue at the bottom (default true); an object is passed to scroll_down
 *                      (e.g. { "target": "#about" })
 *   class, id  string  optional
 * children: extra overlay components between the video and the content (e.g. overlay_noise, animation_*)
 */
class hero_home
{
    private const ALLOWED_TAGS = '<br><b><strong><i><em>';

    public static function render(array $data = []): string
    {
        $background = is_array($data['video'] ?? null)
            ? (string) PlatformComponentRenderer::render('video_background', array_merge(['overlay' => 0.65], $data['video']))
            : '';

        $overlays = section::render_children(array_merge($data, ['content' => '', 'body_content' => '', 'children_html' => '']));

        $eyebrow = trim((string) ($data['eyebrow'] ?? ''));

        // "*word*" -> highlighted span
        $title = self::e(trim((string) ($data['title'] ?? '')));
        $title = preg_replace('/\*([^*]+)\*/', '<span class="hero-home-highlight">$1</span>', $title);

        $text = '';
        foreach ((array) ($data['text'] ?? []) as $p) {
            $p = trim(strip_tags((string) $p, self::ALLOWED_TAGS));
            if ($p !== '') $text .= '<p class="hero-home-text">' . $p . '</p>';
        }

        $buttons = '';
        foreach ((array) ($data['buttons'] ?? []) as $button) {
            if (!is_array($button)) continue;
            $button['class'] = trim((string) ($button['class'] ?? 'btn btn-outline') . ' hero-home-button');
            $buttons .= (string) PlatformComponentRenderer::render('button', $button);
        }

        // scroll cue -> scroll_down component; "scroll": true | false | { scroll_down data }
        $scrollData = $data['scroll'] ?? true;
        $scroll = $scrollData !== false
            ? (string) PlatformComponentRenderer::render('scroll_down', array_merge(
                ['class' => 'hero-home-scroll'],
                is_array($scrollData) ? $scrollData : []
            ))
            : '';

        $align = strtolower((string) ($data['align'] ?? 'center')) === 'left' ? 'left' : 'center';
        $height = in_array($data['height'] ?? '', ['lg', 'md'], true) ? $data['height'] : 'full';
        $class = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => ($id !== '' ? ' id="' . self::e($id) . '"' : '') . ' data-align="' . $align . '" data-height="' . $height . '"',
            'background' => $background,
            'overlays' => trim($overlays) !== '' ? '<div class="hero-home-overlays" aria-hidden="true">' . $overlays . '</div>' : '',
            'eyebrow' => $eyebrow !== '' ? '<p class="hero-home-eyebrow">' . self::e($eyebrow) . '</p>' : '',
            'title' => $title,
            'text' => $text,
            'buttons' => $buttons !== '' ? '<div class="hero-home-actions">' . $buttons . '</div>' : '',
            'scroll' => $scroll,
        ]);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
