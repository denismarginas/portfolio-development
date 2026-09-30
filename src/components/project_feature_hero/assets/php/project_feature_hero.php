<?php

/**
 * Project page hero (rebuilt from the old project_feature_hero).
 *
 *   { "component": "project_feature_hero" }
 *
 * data:
 *   show_web      bool    website devices (default: when taxonomy.tag has "web")
 *   show_photo    bool    photo tablet    (default: when taxonomy.tag has "photo")
 *   show_video    bool    video tablet    (default: when taxonomy.tag has "video")
 *   web_params    object  extra data for card_post_project_visual_devices_website
 *                         (defaults: devices_conditions_render true, desktop/phone_layout_style false)
 *   media_params  object  extra data for card_post_project_visual_devices_media
 *   waves         bool    animated waves at the bottom (default true)
 *   class         string  optional extra class
 *
 * Background: overlay_noise texture + the shapes from data_content_post_projects.json
 * (img.background.overlay_shape_1 / overlay_shape_2).
 * Renders nothing when no device has something to show.
 */
class project_feature_hero
{
    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    /** Website devices in the hero: auto-picked models, no screen layout classes (web_params can override). */
    private const WEB_DEFAULTS = [
        'devices_conditions_render' => true,
        'desktop_layout_style' => false,
        'phone_layout_style' => false,
    ];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $tags = array_map('strtolower', array_map('strval', (array) ($postData['taxonomy']['tag'] ?? [])));
        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $devicesWeb = '';
        if (self::flag($data, 'show_web', in_array('web', $tags, true))) {
            $devicesWeb = (string) PlatformComponentRenderer::render(
                'card_post_project_visual_devices_website',
                array_merge(self::WEB_DEFAULTS, self::params($data, 'web_params'), $shared, ['link' => false])
            );
        }

        $showPhoto = self::flag($data, 'show_photo', in_array('photo', $tags, true));
        $showVideo = self::flag($data, 'show_video', in_array('video', $tags, true));
        $devicesMedia = '';
        if ($showPhoto || $showVideo) {
            $devicesMedia = (string) PlatformComponentRenderer::render(
                'card_post_project_visual_devices_media',
                array_merge(self::params($data, 'media_params'), $shared, [
                    'link' => false,
                    'show' => $showPhoto && $showVideo ? 'both' : ($showPhoto ? 'photo' : 'video'),
                ])
            );
        }

        if (trim($devicesWeb . $devicesMedia) === '') {
            return '';
        }

        $background = self::background();
        $classes = [];
        if ($devicesWeb !== '' && $devicesMedia !== '') $classes[] = 'project-feature-hero-web-and-media';
        $extra = trim((string) ($data['class'] ?? ''));
        if ($extra !== '') $classes[] = $extra;

        return PlatformTemplateRenderer::render([
            'extra_class' => $classes ? ' ' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') : '',
            'texture' => (string) PlatformComponentRenderer::render('overlay_noise', []),
            'shape_1' => self::shape($background['shape_1'], 'project-feature-hero-shape project-feature-hero-shape-1'),
            'shape_2' => self::shape($background['shape_2'], 'project-feature-hero-shape project-feature-hero-shape-2'),
            'devices_web' => $devicesWeb,
            'devices_media' => $devicesMedia,
            'waves' => self::flag($data, 'waves', true) ? (string) PlatformComponentRenderer::render('animation_waves', []) : '',
        ]);
    }

    /** @return array{texture:string, shape_1:string, shape_2:string} */
    private static function background(): array
    {
        $config = PlatformDataService::get_data('content_post_projects') ?? [];
        $bg = is_array($config['img']['background'] ?? null) ? $config['img']['background'] : [];

        return [
            'texture' => (string) ($bg['overlay_texture'] ?? ''),
            'shape_1' => (string) ($bg['overlay_shape_1'] ?? ''),
            'shape_2' => (string) ($bg['overlay_shape_2'] ?? ''),
        ];
    }

    /**
     * Inline svg with our class on the <svg> itself (the old .dm-shape-1 / .dm-shape-2).
     * Empty "xmlns=" attributes (broken in some svg files) are removed first,
     * otherwise the browser swallows the class into them.
     */
    private static function shape(string $icon, string $class): string
    {
        if ($icon === '') return '';
        $svg = (string) PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => '']);
        if ($svg === '') return '';

        $svg = preg_replace('/\sxmlns=(?=[\s>])/', '', $svg, 1) ?? $svg;
        return preg_replace('/<svg(\s[^>]*)?>/', '<svg$1 class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">', $svg, 1) ?? $svg;
    }

    private static function params(array $data, string $key): array
    {
        $value = $data[$key] ?? [];
        if (is_string($value)) $value = json_decode($value, true); // editor stores objects as JSON text
        return is_array($value) ? $value : [];
    }

    private static function flag(array $data, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $data)) return $default;
        $v = $data[$key];
        if (is_bool($v)) return $v;
        return !in_array(strtolower(trim((string) $v)), ['false', '0', 'no', 'off', ''], true);
    }
}
