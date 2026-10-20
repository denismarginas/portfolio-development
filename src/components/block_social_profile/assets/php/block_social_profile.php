<?php

/**
 * Profile header: wallpaper + round avatar + title and description box (old dm-blog-user).
 *
 *   {
 *     "component": "block_social_profile",
 *     "data": {
 *       "wallpaper": "src/content/img/projects/denismarginas/media/banner-linkedin/banner-linkedin-01.webp",
 *       "avatar": "src/content/img/projects/denismarginas/media/logo/logo3.webp",
 *       "title": "Denis Marginas",
 *       "description": "@data.seo.description"
 *     }
 *   }
 *
 * data:
 *   wallpaper    string  wide image ("src/..." path or relative to src/content/img/)
 *   avatar       string  square image shown in the circle
 *   title        string  name / heading ("@data.seo.title" = value of the current page)
 *   title_tag    string  "h1" (default) | "h2"
 *   layout       string  "row" (default: avatar next to the box) | "stacked" (avatar centered over the box,
 *                        for a narrow column such as a section_layout_aside aside; row again below 1024px)
 *   description  string  short text under the title ("@data.seo.description" = value of the current page)
 *   class, id    string  optional
 */
class block_social_profile
{
    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        $title = self::field($data['title'] ?? '', $post);
        $description = self::field($data['description'] ?? '', $post);
        $wallpaper = self::str($data['wallpaper'] ?? '');
        $avatar = self::str($data['avatar'] ?? '');
        if ($title === '' && $wallpaper === '') return '';

        $class = trim((self::str($data['layout'] ?? '') === 'stacked' ? 'block-social-profile-stacked ' : '') . self::str($data['class'] ?? ''));
        $id = self::str($data['id'] ?? '');

        return PlatformTemplateRenderer::render([
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'wallpaper' => $wallpaper !== '' ? (string) PlatformComponentRenderer::render('image', ['src' => $wallpaper, 'alt' => '', 'class' => 'block-social-profile-wallpaper-img']) : '',
            'avatar' => $avatar !== '' ? (string) PlatformComponentRenderer::render('image', ['src' => $avatar, 'alt' => $title, 'class' => 'block-social-profile-avatar-img']) : '',
            'tag' => self::str($data['title_tag'] ?? '') === 'h2' ? 'h2' : 'h1',
            'title' => self::e($title),
            'description' => $description !== '' ? '<p class="block-social-profile-description">' . self::e($description) . '</p>' : '',
        ]);
    }

    /** Text, or "@path" -> value of the current page (like hero). */
    private static function field(mixed $raw, array $post): string
    {
        $raw = self::str($raw);
        if (!str_starts_with($raw, '@')) return $raw;
        $path = substr($raw, 1);
        $value = PlatformDataService::resolve_path_string($post, $path);
        if ($value === null && str_starts_with($path, 'data.')) $value = PlatformDataService::resolve_path_string($post, substr($path, 5));
        return self::str($value);
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
