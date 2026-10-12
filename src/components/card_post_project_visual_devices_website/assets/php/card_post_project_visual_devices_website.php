<?php

require_once __DIR__ . '/parts/project_device_render_conditions.php';
require_once __DIR__ . '/parts/project_desktop_layout_style.php';
require_once __DIR__ . '/parts/project_device_models.php';
require_once __DIR__ . '/parts/project_device_blocks.php';
require_once __DIR__ . '/../../../utility_project_web_screens/assets/php/utility_project_web_screens.php';

/**
 * Laptop + phone mockup with the project's website screenshots.
 *
 * $data options:
 *   post_current_data          array   project post (required)
 *   desktop_device / phone_device  string  force a model ("model-01|02|03")
 *   devices_conditions_render  bool    auto-pick models by type/year (default true)
 *   desktop_layout_style       bool    add top/center + bg classes to desktop screen (default true)
 *   phone_layout_style         bool    same, for the phone screen (default true)
 *   screen_desktop             string  optional fixed desktop screenshot, relative to screen_path
 *   screen_phone               string  optional fixed phone screenshot, relative to screen_path
 *   screen_path                string  base folder for screen_desktop / screen_phone (default "web/website/")
 *                                      Without screen_desktop / screen_phone the cover of
 *                                      web/website/desktop/ and web/website/phone/ is used: the "_0" image
 *                                      (web_desktop_0.webp / web_phone_0.webp), else the first one
 *                                      (utility_project_web_screens).
 *   link                       bool    wrap in <a> to the post (default true), false -> <div>
 *   render_media_image         string  render ONE device with this image instead of the cover
 *                                      screenshots (path relative to src/content/img/projects/,
 *                                      or a full "src/..." path). Default null.
 *   render_media_device_type   string  "desktop" | "phone" - device used for render_media_image
 *                                      (default null -> "desktop")
 *
 * A device is rendered only if its screenshot exists. When both exist,
 * .dm-post-view gets "desktop-and-phone-devices".
 */
class card_post_project_visual_devices_website
{
    private const DEFAULT_SCREEN_PATH = 'web/website/';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) {
            return '';
        }

        $postData = is_array($post['data'] ?? null) ? $post['data'] : [];

        $models = project_device_models::resolve($data, $postData);
        $frames = project_device_models::frames($models);

        // Single image on a single device (used by the web galleries), otherwise the cover screenshots
        $mediaImage = trim((string) ($data['render_media_image'] ?? ''));
        if ($mediaImage === '') {
            $screens = self::resolve_screens($data, (string) ($postData['media']['path'] ?? ''));
        } else {
            $type = strtolower(trim((string) ($data['render_media_device_type'] ?? ''))) === 'phone' ? 'phone' : 'desktop';
            $path = str_starts_with($mediaImage, 'src/') ? $mediaImage : 'src/content/img/projects/' . ltrim($mediaImage, '/');
            $screens = ['desktop' => $type === 'desktop' ? $path : '', 'phone' => $type === 'phone' ? $path : ''];
        }

        $desktopBlock = project_device_blocks::desktop($screens['desktop'], $frames['desktop'], $models['desktop'], self::flag($data, 'desktop_layout_style'));
        $phoneBlock = project_device_blocks::phone($screens['phone'], $frames['phone'], $models['phone'], self::flag($data, 'phone_layout_style'));

        if ($desktopBlock === '' && $phoneBlock === '') {
            return '';
        }

        $asLink = self::flag($data, 'link');
        $link = PlatformPathService::post_link((string) ($post['_id'] ?? ''));

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'tag' => $asLink ? 'a' : 'div',
            'href_attr' => $asLink ? ' href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '"' : '',
            'both_devices_check' => ($desktopBlock !== '' && $phoneBlock !== '') ? ' desktop-and-phone-devices' : '',
            'desktop_block' => $desktopBlock,
            'phone_block' => $phoneBlock,
        ]);
    }

    /** Boolean option, true when missing. Accepts real booleans and "false"/"0"/"no"/"off" text. */
    private static function flag(array $data, string $key): bool
    {
        if (!array_key_exists($key, $data)) return true;
        $v = $data[$key];
        if (is_bool($v)) return $v;
        return !in_array(strtolower(trim((string) $v)), ['false', '0', 'no', 'off', ''], true);
    }

    /** @return array{desktop:string, phone:string} */
    private static function resolve_screens(array $data, string $mediaPath): array
    {
        if ($mediaPath === '') {
            return ['desktop' => '', 'phone' => ''];
        }

        $screenPath = rtrim((string) ($data['screen_path'] ?? self::DEFAULT_SCREEN_PATH), '/') . '/';
        $base = utility_project_web_screens::PROJECTS_DIR . $mediaPath . '/' . $screenPath;

        $screens = [];
        foreach (utility_project_web_screens::DEVICES as $device) {
            $fixed = trim((string) ($data['screen_' . $device] ?? ''));
            $screens[$device] = $fixed !== ''
                ? $base . ltrim($fixed, '/')
                : utility_project_web_screens::first($mediaPath, $device);
        }
        return $screens;
    }
}
