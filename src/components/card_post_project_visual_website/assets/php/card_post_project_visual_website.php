<?php

require_once __DIR__ . '/parts/project_device_render_conditions.php';
require_once __DIR__ . '/parts/project_desktop_layout_style.php';
require_once __DIR__ . '/parts/project_device_models.php';
require_once __DIR__ . '/parts/project_device_blocks.php';

/**
 * Laptop + phone mockup with the project's website screenshots.
 *
 * $data options:
 *   post_current_data          array   project post (required)
 *   desktop_device / phone_device  string  force a model ("model-01|02|03")
 *   devices_conditions_render  bool    auto-pick models by type/year (default true)
 *   desktop_layout_style       bool    add top/center + bg classes to desktop screen (default true)
 *   phone_layout_style         bool    same, for the phone screen (default true)
 *   screen_path                string  default "web/overview/"
 *   screen_desktop             string  default "web_desktop_overview.webp"
 *   screen_phone               string  default "web_phone_overview.webp"
 *
 * A device is rendered only if its screenshot exists. When both exist,
 * .dm-post-view gets "desktop-and-phone-devices".
 */
class card_post_project_visual_website
{
    private const DEFAULT_SCREEN_PATH = 'web/overview/';
    private const DEFAULT_SCREEN_DESKTOP = 'web_desktop_overview.webp';
    private const DEFAULT_SCREEN_PHONE = 'web_phone_overview.webp';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];
        $primaryColor = (string) ($post['settings']['appearance']['colors']['primary'] ?? '');
        $color = htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8');

        $models = project_device_models::resolve($data, $postData);
        $frames = project_device_models::frames($models);
        $screens = self::resolve_screens($data, (string) ($postData['media']['path'] ?? ''));
        $screenStyle = $color !== '' ? ' style="background-color: ' . $color . ';"' : '';

        $desktopBlock = project_device_blocks::desktop($screens['desktop'], $frames['desktop'], $models['desktop'], $screenStyle, self::flag($data, 'desktop_layout_style'));
        $phoneBlock = project_device_blocks::phone($screens['phone'], $frames['phone'], $models['phone'], $screenStyle, self::flag($data, 'phone_layout_style'));

        $link = PlatformPathService::post_link((string) ($post['_id'] ?? '')) . '#webdevelopment';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'both_devices_check' => ($desktopBlock !== '' && $phoneBlock !== '') ? ' desktop-and-phone-devices' : '',
            'link' => htmlspecialchars($link, ENT_QUOTES, 'UTF-8'),
            'color_style' => $color !== '' ? ' style="--primary-color-post: ' . $color . ';"' : '',
            'desktop_block' => $desktopBlock,
            'phone_block' => $phoneBlock,
        ]);
    }

    /** Boolean option, true when missing. */
    private static function flag(array $data, string $key): bool
    {
        return !array_key_exists($key, $data) || (bool) $data[$key];
    }

    /** @return array{desktop:string, phone:string} */
    private static function resolve_screens(array $data, string $mediaPath): array
    {
        if ($mediaPath === '') {
            return ['desktop' => '', 'phone' => ''];
        }

        $screenPath = rtrim((string) ($data['screen_path'] ?? self::DEFAULT_SCREEN_PATH), '/') . '/';
        $base = 'src/content/img/projects/' . $mediaPath . '/' . $screenPath;

        return [
            'desktop' => $base . (string) ($data['screen_desktop'] ?? self::DEFAULT_SCREEN_DESKTOP),
            'phone' => $base . (string) ($data['screen_phone'] ?? self::DEFAULT_SCREEN_PHONE),
        ];
    }
}
