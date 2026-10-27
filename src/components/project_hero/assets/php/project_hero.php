<?php

require_once __DIR__ . '/../../../utility_project_media/assets/php/utility_project_media.php';

/**
 * Project page hero (h1 of the page), built from the post data.
 *
 *   { "component": "project_hero" }
 *
 * data (all optional):
 *   variant   string  "auto" (default) | "website" | "media" | "simple"
 *                     auto: website screenshots (web/website/desktop) -> "website", else photos / videos -> "media",
 *                     else "simple" (the thumbnail as stage)
 *   title     string  default data.seo.title (first word on its own line, the last word in the post color)
 *   lead      string  default data.seo.description
 *   eyebrow   string  default: first project type + end date ("Online shop · Dec 2021")
 *   actions   array   buttons { "text", "link", "style": "accent" | "ghost", "svg", "target" }
 *                     default: "See the screens" / "Browse the gallery" (to the section) + "Visit website" (first web url)
 *   meta      array   { "label", "value" } rows under the buttons (default: Platform / Scope / Status for websites;
 *                     media pages show photo / video / gallery counts instead)
 *   logo      bool    project logo badge above the eyebrow (default false)
 *   targets   object  section ids the buttons and stage point to (default { "screens": "screens", "gallery": "gallery",
 *                     "overview": "overview" })
 *   id        string  optional (default "top")
 */
class project_hero
{
    private const HTML_DIR = __DIR__ . '/../html/';
    private const VARIANTS = ['website', 'media', 'simple'];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) return '';
        $d = is_array($post['data'] ?? null) ? $post['data'] : [];

        $title = self::str($data['title'] ?? '') ?: self::str($d['seo']['title'] ?? '');
        if ($title === '') return '';

        $targets = array_merge(['screens' => 'screens', 'gallery' => 'gallery', 'overview' => 'overview'], is_array($data['targets'] ?? null) ? $data['targets'] : []);
        $variant = self::variant($data, $post);

        $lead = self::str($data['lead'] ?? '') ?: self::str($d['seo']['description'] ?? '');
        $id = array_key_exists('id', $data) ? self::str($data['id']) : 'top';

        return PlatformTemplateRenderer::render([
            'variant' => $variant,
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'brand' => ($data['logo'] ?? false) === true ? self::brand($post, $title) : '',
            'eyebrow' => self::eyebrow($data, $d),
            'title' => self::title($title),
            'lead' => $lead !== '' ? '<p class="project-hero-lead">' . self::e(strip_tags($lead)) . '</p>' : '',
            'actions' => self::actions($data, $d, $variant, $targets),
            'meta' => $variant === 'media' ? self::stats($post) : self::meta($data, $d),
            'stage' => self::stage($variant, $post, $title, $targets),
        ]);
    }

    private static function variant(array $data, array $post): string
    {
        $variant = strtolower(self::str($data['variant'] ?? 'auto'));
        if (in_array($variant, self::VARIANTS, true)) return $variant;
        if (utility_project_media::screens($post, 'desktop') !== []) return 'website';
        if (utility_project_media::images($post, 'media') !== [] || utility_project_media::videos($post) !== []) return 'media';
        return 'simple';
    }

    /** Logo badge on the post canvas color. */
    private static function brand(array $post, string $title): string
    {
        $logo = utility_project_media::logo($post);
        $html = $logo['img'] !== ''
            ? (string) PlatformComponentRenderer::render('image', ['src' => $logo['img'], 'alt' => 'Logo - ' . $title, 'class' => 'project-hero-brand-img'])
            : ($logo['svg'] !== '' ? (string) PlatformComponentRenderer::render('svg', ['icon' => $logo['svg'], 'class' => 'project-hero-brand-img']) : '');
        return trim($html) !== '' ? '<span class="project-hero-brand">' . $html . '</span>' : '';
    }

    /** "Online shop · Dec 2021" (first type + end date, else start date). */
    private static function eyebrow(array $data, array $d): string
    {
        $parts = [];
        if (($custom = self::str($data['eyebrow'] ?? '')) !== '') {
            $parts[] = $custom;
        } else {
            $type = self::strings($d['project']['types'] ?? [])[0] ?? '';
            if ($type !== '') $parts[] = self::sentence($type);
            $date = self::date(self::str($d['date']['end'] ?? '')) ?: self::date(self::str($d['date']['start'] ?? ''));
            if ($date !== '') $parts[] = $date;
        }

        $html = '<span class="project-hero-dot" aria-hidden="true"></span>';
        foreach ($parts as $part) $html .= '<span>' . self::e($part) . '</span>';
        return $html;
    }

    /** "Agramix Online Shop" -> <span main>Agramix</span><span rest>Online <em>Shop</em></span> */
    private static function title(string $title): string
    {
        $words = preg_split('/\s+/u', trim($title)) ?: [];
        if (count($words) === 1) return '<span class="project-hero-title-main"><em>' . self::e($words[0]) . '</em></span>';

        $first = array_shift($words);
        $last = array_pop($words);
        $rest = ($words !== [] ? self::e(implode(' ', $words)) . ' ' : '') . '<em>' . self::e((string) $last) . '</em>';
        return '<span class="project-hero-title-main">' . self::e($first) . '</span> <span class="project-hero-title-rest">' . $rest . '</span>';
    }

    private static function actions(array $data, array $d, string $variant, array $targets): string
    {
        $actions = is_array($data['actions'] ?? null) ? $data['actions'] : null;
        if ($actions === null) {
            $actions = [];
            if ($variant === 'website') {
                $actions[] = ['text' => utility_project_media::text('hero_see_screens', 'See the screens'), 'link' => '#' . $targets['screens'], 'style' => 'accent', 'svg' => 'arrow-right'];
            } elseif ($variant === 'media') {
                $actions[] = ['text' => utility_project_media::text('hero_browse_gallery', 'Browse the gallery'), 'link' => '#' . $targets['gallery'], 'style' => 'accent', 'svg' => 'arrow-right'];
            } else {
                $actions[] = ['text' => utility_project_media::text('hero_details', 'Project details'), 'link' => '#' . $targets['overview'], 'style' => 'accent', 'svg' => 'arrow-right'];
            }
            $url = self::str(($d['web']['urls'][0]['url'] ?? '') ?: '');
            if ($url !== '') {
                $actions[] = [
                    'text' => utility_project_media::text('hero_visit_website', 'Visit website'),
                    'link' => (string) PlatformComponentRenderer::value('utility_string_urlto', ['input' => $url, 'action' => 'transform']),
                    'style' => 'ghost',
                    'svg' => 'new-tab',
                    'target' => '_blank',
                ];
            }
        }

        $html = '';
        foreach ($actions as $action) {
            if (!is_array($action)) continue;
            $style = self::str($action['style'] ?? 'accent') === 'ghost' ? 'btn-ghost' : 'btn-accent';
            $target = self::str($action['target'] ?? '');
            $html .= (string) PlatformComponentRenderer::render('button', [
                'text' => self::str($action['text'] ?? ''),
                'link' => $action['link'] ?? '',
                'svg' => self::str($action['svg'] ?? ''),
                'class' => 'btn ' . $style . ' project-hero-btn',
                'target' => $target,
                'rel' => $target === '_blank' ? 'noopener' : '',
            ]);
        }
        return $html !== '' ? '<div class="project-hero-actions">' . $html . '</div>' : '';
    }

    /** Website meta: Platform / Scope / Status (or data.meta). */
    private static function meta(array $data, array $d): string
    {
        $items = is_array($data['meta'] ?? null) ? $data['meta'] : null;
        if ($items === null) {
            $platform = $d['web']['platform'][0]['text'] ?? '';
            $type = self::strings($d['project']['types'] ?? [])[0] ?? '';
            $status = '';
            if (array_key_exists('delivery_status', $d['web'] ?? [])) {
                $done = filter_var($d['web']['delivery_status'], FILTER_VALIDATE_BOOLEAN);
                $status = $done ? utility_project_media::text('status_done', 'Delivered') : utility_project_media::text('status_undone', 'In progress');
            }
            $items = [
                ['label' => utility_project_media::text('meta_platform', 'Platform'), 'value' => self::str($platform)],
                ['label' => utility_project_media::text('meta_scope', 'Scope'), 'value' => self::sentence($type)],
                ['label' => utility_project_media::text('meta_status', 'Status'), 'value' => $status],
            ];
        }
        return (string) PlatformComponentRenderer::render('key_value_list', [
            'items' => $items,
            'variant' => 'inline',
            'class' => 'project-hero-meta',
        ]);
    }

    /** Media stats: photos, videos, galleries (only the ones that exist). */
    private static function stats(array $post): string
    {
        $galleries = utility_project_media::galleries($post, 'media');
        $photos = array_sum(array_map(fn ($g) => count($g['images']), $galleries));
        $videos = count(utility_project_media::videos($post));

        $stats = [
            [$photos, utility_project_media::text('stat_photos', 'Photos')],
            [$videos, utility_project_media::text('stat_videos', 'Videos')],
            [count($galleries), utility_project_media::text('stat_galleries', 'Galleries')],
        ];
        $html = '';
        foreach ($stats as [$count, $label]) {
            if ($count < 1) continue;
            $html .= '<div class="project-hero-stat"><b>' . str_pad((string) $count, 2, '0', STR_PAD_LEFT) . '</b><span>' . self::e($label) . '</span></div>';
        }
        return $html !== '' ? '<div class="project-hero-stats">' . $html . '</div>' : '';
    }

    private static function stage(string $variant, array $post, string $title, array $targets): string
    {
        return match ($variant) {
            'website' => self::stage_website($post, $title, $targets),
            'media' => self::stage_media($post, $title, $targets),
            default => self::stage_simple($post, $title),
        };
    }

    /** Cover desktop screenshot in a browser frame + the phone cover. */
    private static function stage_website(array $post, string $title, array $targets): string
    {
        $desktop = utility_project_media::screens($post, 'desktop')[0] ?? '';
        $phone = utility_project_media::screens($post, 'phone')[0] ?? '';
        $phoneHtml = $phone !== ''
            ? '<span class="project-hero-phone">' . PlatformComponentRenderer::render('image', ['src' => $phone, 'alt' => $title . ' - mobile']) . '</span>'
            : '';

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/stage_website.html', [
            'href' => '#' . self::e((string) $targets['screens']),
            'label' => self::e(utility_project_media::text('hero_see_screens', 'See the screens')),
            'url' => '<span class="project-hero-browser-url">' . self::e(self::short($title)) . '</span>',
            'desktop' => (string) PlatformComponentRenderer::render('image', ['src' => $desktop, 'alt' => $title . ' - desktop']),
            'phone' => $phoneHtml,
            'tag' => self::e($phone !== '' ? utility_project_media::text('hero_tag_website', 'Desktop + mobile') : utility_project_media::text('hero_tag_desktop', 'Desktop')),
        ]);
    }

    /** A landscape gallery image in a window, a square image (or the logo) and the first video as a card (popup). */
    private static function stage_media(array $post, string $title, array $targets): string
    {
        $images = utility_project_media::images($post, 'media');
        // the 2nd image of the first gallery folder (the 1st is often a logo / intro frame), else the 1st
        $first = utility_project_media::galleries($post, 'media')[0]['images'] ?? [];
        $main = ($first[1] ?? $first[0] ?? '') ?: utility_project_media::thumbnail($post);
        $square = self::pick(array_values(array_diff($images, [$main])), 0.85, 1.2);

        $mainHtml = $main !== '' ? (string) PlatformComponentRenderer::render('image', ['src' => $main, 'alt' => $title]) : '';
        if (trim($mainHtml) === '') return self::stage_simple($post, $title);

        $squareHtml = '';
        if ($square !== '') {
            $squareHtml = (string) PlatformComponentRenderer::render('image', ['src' => $square, 'alt' => $title, 'lazy' => true]);
        } else {
            $logo = utility_project_media::logo($post);
            if ($logo['img'] !== '') $squareHtml = (string) PlatformComponentRenderer::render('image', ['src' => $logo['img'], 'alt' => 'Logo - ' . $title, 'class' => 'project-hero-square-logo']);
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/stage_media.html', [
            'href' => '#' . self::e((string) $targets['gallery']),
            'label' => self::e(utility_project_media::text('hero_browse_gallery', 'Browse the gallery')),
            'window_title' => self::e(self::short($title)),
            'main' => $mainHtml,
            'square' => trim($squareHtml) !== '' ? '<span class="project-hero-square">' . $squareHtml . '</span>' : '',
            'video' => self::video_card($post),
            'tag' => self::e(implode(' · ', array_map([self::class, 'sentence'], array_slice(self::strings($post['data']['taxonomy']['tag'] ?? []), 0, 3))) ?: $title),
        ]);
    }

    /** First project video: thumbnail (or a mode colored box) + play button; opens the player in the popup. */
    private static function video_card(array $post): string
    {
        $video = utility_project_media::videos($post)[0] ?? null;
        if ($video === null) return '';

        $player = (string) PlatformComponentRenderer::render('project_block_video', [
            'post_current_data' => $post,
            'src' => $video['src'],
            'video_primary_color' => $video['color'] ?: self::str($post['settings']['appearance']['colors']['primary'] ?? ''),
        ]);
        if (trim($player) === '') return '';

        $thumb = $video['thumbnail'] !== ''
            ? (string) PlatformComponentRenderer::render('image', ['src' => $video['thumbnail'], 'alt' => '', 'lazy' => true])
            : '';
        $trigger = '<button type="button" class="project-hero-video' . ($thumb === '' ? ' project-hero-video-no-thumb' : '') . '" aria-label="' . self::e(utility_project_media::text('play', 'Play') . ': ' . $video['title']) . '">'
            . $thumb
            . '<span class="project-hero-play" aria-hidden="true">' . self::play_icon() . '</span>'
            . '<span class="project-hero-video-label">' . self::e($video['title']) . '</span>'
            . '</button>';

        return (string) PlatformComponentRenderer::render('popup', [
            'trigger' => $trigger,
            'content' => '<div class="project-hero-video-popup">' . $player . '</div>',
            'class' => 'project-hero-video-trigger',
        ]);
    }

    /** No media: the thumbnail in a frame on the canvas color. */
    private static function stage_simple(array $post, string $title): string
    {
        $thumb = utility_project_media::thumbnail($post);
        if ($thumb === '') return '';
        $img = (string) PlatformComponentRenderer::render('image', ['src' => $thumb, 'alt' => $title]);
        return trim($img) !== '' ? '<div class="project-hero-stage project-hero-stage-simple"><span class="project-hero-thumb">' . $img . '</span></div>' : '';
    }

    /** The project name for a window / browser bar: cut at 45 characters with "...". */
    private static function short(string $title, int $max = 45): string
    {
        return mb_strlen($title) > $max ? rtrim(mb_substr($title, 0, $max - 1)) . '…' : $title;
    }

    /** First image whose width / height is between $min and $max ('' when none). */
    private static function pick(array $images, float $min, float $max): string
    {
        foreach ($images as $image) {
            $size = utility_project_media::size($image);
            if ($size === null) continue;
            $ratio = $size[0] / $size[1];
            if ($ratio >= $min && $ratio <= $max) return $image;
        }
        return '';
    }

    public static function play_icon(): string
    {
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8,5.14V19.14L19,12.14L8,5.14Z"/></svg>';
    }

    /** "2021-12-01" -> "Dec 2021" ('' when it is not a date). */
    private static function date(string $value): string
    {
        if (!preg_match('/^\d{4}-\d{1,2}(-\d{1,2})?$/', $value)) return '';
        if (substr_count($value, '-') === 1) $value .= '-01';
        return PlatformTextService::format_date($value, 'M Y');
    }

    private static function sentence(string $text): string
    {
        $text = trim(str_replace(['-', '_'], ' ', $text));
        return $text === '' ? '' : mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }

    private static function strings(mixed $value): array
    {
        if (is_string($value)) $value = [$value];
        if (!is_array($value)) return [];
        return array_values(array_filter(array_map(fn ($v) => is_scalar($v) ? trim((string) $v) : '', $value), 'strlen'));
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
