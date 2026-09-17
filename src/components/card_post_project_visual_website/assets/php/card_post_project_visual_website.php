<?php

require_once __DIR__ . '/parts/project_device_render_conditions.php';
require_once __DIR__ . '/parts/project_desktop_layout_style.php';

/**
 * card_post_project_visual_website
 *
 * Visual for a project card when the post belongs to the
 * "Web Development Projects" category: a laptop + phone device mockup
 * showing the project's website screenshots.
 *
 * Expected $data:
 *   'post_current_data' => array   full project post entry (required)
 *   'desktop_device' => string  force a specific model key ("model-01",
 *                                "model-02" or "model-03") into
 *                                data_content_post_projects.json's
 *                                img.devices.desktop map, instead of
 *                                auto-picking one. Default: null (auto).
 *   'phone_device'   => string  same as above, for img.devices.phone.
 *                                Default: null (auto).
 *   'devices_conditions_render' => bool  when true (default), the desktop
 *                                and phone device models are auto-picked
 *                                per project via
 *                                project_device_render_conditions (project
 *                                type + start year). When false, the
 *                                conditions are skipped entirely and both
 *                                devices fall back to "model-03". An
 *                                explicit 'desktop_device' / 'phone_device'
 *                                override above always wins regardless of
 *                                this flag.
 *   'desktop_layout_style' => bool  when true (default), the desktop
 *                                screenshot's actual file is inspected
 *                                (via project_desktop_layout_style) to add
 *                                a "top"/"center" anchor class plus a
 *                                bg-primary/bg-white utility class to the
 *                                desktop .screen element. When false, none
 *                                of that is computed and no extra classes
 *                                are added.
 *   'screen_path'    => string  folder (relative to the project's image
 *                                folder) holding the screenshots.
 *                                Default: "web/overview/".
 *   'screen_desktop' => string  desktop screenshot filename.
 *                                Default: "web_desktop_overview.webp".
 *   'screen_phone'   => string  phone screenshot filename.
 *                                Default: "web_phone_overview.webp".
 *
 * Whichever model got picked for each device type is also exposed as an
 * extra class on that device's wrapper, e.g.
 * <div class="device-layout-laptop desktop-model-02"> /
 * <div class="device-layout-phone phone-model-01"> - matching the legacy
 * $classDevice = " " . $type . "-model-0X" convention.
 *
 * If a project has no phone screenshot (file missing on disk), the whole
 * .device-layout-phone block is omitted - the desktop mockup still renders
 * on its own.
 */
class card_post_project_visual_website
{
    private const DEFAULT_SCREEN_PATH = 'web/overview/';
    private const DEFAULT_SCREEN_DESKTOP = 'web_desktop_overview.webp';
    private const DEFAULT_SCREEN_PHONE = 'web_phone_overview.webp';
    private const FORCED_DEVICE_MODEL = 'model-03';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $settings = is_array($post['settings'] ?? null) ? $post['settings'] : [];

        $mediaPath = (string) ($postData['media']['path'] ?? '');
        $primaryColor = (string) ($settings['appearance']['colors']['primary'] ?? '');

        $devicesEnabled = !array_key_exists('devices_conditions_render', $data) || (bool) $data['devices_conditions_render'];
        $layoutStyleEnabled = !array_key_exists('desktop_layout_style', $data) || (bool) $data['desktop_layout_style'];

        $deviceModels = self::resolve_device_models($data, $postData, $devicesEnabled);
        $frames = self::resolve_device_frames($deviceModels);
        $screens = self::resolve_screens($data, $mediaPath);

        $link = PlatformPathService::post_link((string) ($post['_id'] ?? '')) . '#webdevelopment';

        $colorStyle = $primaryColor !== ''
            ? ' style="--primary-color-post: ' . htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8') . ';"'
            : '';
        $screenStyle = $primaryColor !== ''
            ? ' style="background-color: ' . htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8') . ';"'
            : '';

        $desktopImage = $screens['desktop'] !== ''
            ? PlatformComponentRenderer::render('image', [
                'src' => $screens['desktop'],
                'alt' => 'Website preview',
                'class' => 'web-desktop-image',
            ])
            : '';
        $phoneImage = $screens['phone'] !== ''
            ? PlatformComponentRenderer::render('image', [
                'src' => $screens['phone'],
                'alt' => 'Website preview mobile',
                'class' => 'web-phone-image',
            ])
            : '';
        $laptopFrame = $frames['desktop'] !== ''
            ? PlatformComponentRenderer::render('image', [
                'src' => $frames['desktop'],
                'alt' => 'Laptop frame',
                'class' => 'laptop',
            ])
            : '';
        $phoneFrame = $frames['phone'] !== ''
            ? PlatformComponentRenderer::render('image', [
                'src' => $frames['phone'],
                'alt' => 'Phone frame',
                'class' => 'phone',
            ])
            : '';

        $desktopDeviceClass = $deviceModels['desktop'] !== '' ? ' desktop-' . $deviceModels['desktop'] : '';
        $phoneDeviceClass = $deviceModels['phone'] !== '' ? ' phone-' . $deviceModels['phone'] : '';

        $desktopScreenClass = 'screen';
        $desktopScreenAttrs = '';
        if ($layoutStyleEnabled) {
            $layout = project_desktop_layout_style::resolve('desktop', $screens['desktop']);
            if ($layout['classes'] !== '') {
                $desktopScreenClass .= ' ' . $layout['classes'];
            }
            if ($layout['aspect_ratio'] !== null) {
                $desktopScreenAttrs = ' data-aspect-ratio="' . htmlspecialchars((string) $layout['aspect_ratio'], ENT_QUOTES, 'UTF-8') . '"';
            }
        }

        $desktopBlock = PlatformTemplateRenderer::render(__DIR__ . '/../html/parts/desktop_block.html', [
            'device_class' => htmlspecialchars($desktopDeviceClass, ENT_QUOTES, 'UTF-8'),
            'screen_class' => htmlspecialchars($desktopScreenClass, ENT_QUOTES, 'UTF-8'),
            'screen_style' => $screenStyle,
            'screen_attrs' => $desktopScreenAttrs,
            'desktop_image' => $desktopImage,
            'laptop_frame' => $laptopFrame,
        ]);

        // The "image" component itself returns '' when the file doesn't
        // exist on disk (see image_render.php), so $phoneImage being empty
        // means this project genuinely has no phone screenshot. Skip the
        // whole device-layout-phone block in that case - don't show an
        // empty phone frame - even though the desktop one rendered fine.
        $phoneBlock = '';
        if ($phoneImage !== '') {
            $phoneBlock = PlatformTemplateRenderer::render(__DIR__ . '/../html/parts/phone_block.html', [
                'device_class' => htmlspecialchars($phoneDeviceClass, ENT_QUOTES, 'UTF-8'),
                'screen_style' => $screenStyle,
                'phone_image' => $phoneImage,
                'phone_frame' => $phoneFrame,
            ]);
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'color_style' => $colorStyle,
            'desktop_block' => $desktopBlock,
            'phone_block' => $phoneBlock,
        ]);
    }

    /**
     * Picks the model key ("model-01"/"model-02"/"model-03") to use for
     * each device type. An explicit 'desktop_device' / 'phone_device'
     * override always wins for that device; otherwise, when
     * $devicesEnabled is true the pick comes from
     * project_device_render_conditions, and when false both devices are
     * forced to FORCED_DEVICE_MODEL.
     *
     * @return array{desktop:string, phone:string}
     */
    private static function resolve_device_models(array $data, array $postData, bool $devicesEnabled): array
    {
        $desktopOverride = isset($data['desktop_device']) && $data['desktop_device'] !== '' ? (string) $data['desktop_device'] : null;
        $phoneOverride = isset($data['phone_device']) && $data['phone_device'] !== '' ? (string) $data['phone_device'] : null;

        if ($devicesEnabled) {
            $config = PlatformDataService::get_data('content_post_projects');
            $devices = is_array($config['img']['devices'] ?? null) ? $config['img']['devices'] : [];
            $auto = project_device_render_conditions::resolve($postData, [
                'desktop' => is_array($devices['desktop'] ?? null) ? $devices['desktop'] : [],
                'phone' => is_array($devices['phone'] ?? null) ? $devices['phone'] : [],
            ]);
        } else {
            $auto = ['desktop' => self::FORCED_DEVICE_MODEL, 'phone' => self::FORCED_DEVICE_MODEL];
        }

        return [
            'desktop' => $desktopOverride ?? $auto['desktop'],
            'phone' => $phoneOverride ?? $auto['phone'],
        ];
    }

    /**
     * @return array{desktop:string, phone:string}
     */
    private static function resolve_device_frames(array $deviceModels): array
    {
        $config = PlatformDataService::get_data('content_post_projects');
        $devices = is_array($config['img']['devices'] ?? null) ? $config['img']['devices'] : [];
        $desktopModels = is_array($devices['desktop'] ?? null) ? $devices['desktop'] : [];
        $phoneModels = is_array($devices['phone'] ?? null) ? $devices['phone'] : [];

        return [
            'desktop' => (string) ($desktopModels[$deviceModels['desktop']] ?? ''),
            'phone' => (string) ($phoneModels[$deviceModels['phone']] ?? ''),
        ];
    }

    /**
     * @return array{desktop:string, phone:string}
     */
    private static function resolve_screens(array $data, string $mediaPath): array
    {
        if ($mediaPath === '') {
            return ['desktop' => '', 'phone' => ''];
        }

        $screenPath = rtrim((string) ($data['screen_path'] ?? self::DEFAULT_SCREEN_PATH), '/') . '/';
        $screenDesktop = (string) ($data['screen_desktop'] ?? self::DEFAULT_SCREEN_DESKTOP);
        $screenPhone = (string) ($data['screen_phone'] ?? self::DEFAULT_SCREEN_PHONE);

        $base = 'src/content/img/projects/' . $mediaPath . '/' . $screenPath;

        return [
            'desktop' => $base . $screenDesktop,
            'phone' => $base . $screenPhone,
        ];
    }
}
