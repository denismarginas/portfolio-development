<?php

/**
 *   {
 *     "component": "block_image",
 *     "data": { "image_path": "web/auto-filled-request-form/", "image_file_name": "form.webp" }
 *   }
 *
 * data:
 *   image_path       string  folder relative to the post media folder
 *                            (src/content/img/projects/<media.path>/), or a full "src/..." path
 *   image_file_name  string  file name inside image_path (may also be the whole relative path)
 *   alt              string  alt text (default: post title)
 *   caption          string  optional caption under the image
 *   popup_group      string  optional data-popup-group, to browse several blocks in one popup gallery
 *   class            string  optional extra class
 *   id               string  optional id
 *
 * Renders nothing when the image file does not exist.
 */
class block_image
{
    private const PROJECTS_DIR = 'src/content/img/projects/';

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];

        $src = self::source($data, $post);
        if ($src === '') return '';

        $alt = trim((string) ($data['alt'] ?? ($post['data']['project']['title'] ?? $post['data']['seo']['title'] ?? '')));

        $image = (string) PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => $alt,
            'class' => 'block-image-img',
            'lazy' => true,
        ]);
        if (trim($image) === '') return '';

        $caption = trim((string) ($data['caption'] ?? ''));
        $group = trim((string) ($data['popup_group'] ?? ''));
        $extra = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render([
            'extra_class' => $extra !== '' ? ' ' . htmlspecialchars($extra, ENT_QUOTES, 'UTF-8') : '',
            'id_attr' => $id !== '' ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : '',
            'group_attr' => $group !== '' ? ' data-popup-group="' . htmlspecialchars($group, ENT_QUOTES, 'UTF-8') . '"' : '',
            'image' => $image,
            'caption' => $caption !== ''
                ? PlatformTemplateRenderer::render(__DIR__ . '/../html/parts/caption.html', [
                    'caption' => htmlspecialchars($caption, ENT_QUOTES, 'UTF-8', false),
                ])
                : '',
        ]);
    }

    /** Project-relative path of the image, or "" when it can't be found. */
    private static function source(array $data, array $post): string
    {
        $dir = trim(str_replace('\\', '/', (string) ($data['image_path'] ?? '')), '/');
        $file = trim(str_replace('\\', '/', (string) ($data['image_file_name'] ?? '')), '/');
        $relative = trim($dir . '/' . $file, '/');
        if ($file === '' || str_contains($relative, '..')) return '';

        if (str_starts_with($relative, 'src/')) {
            return self::exists($relative) ? $relative : '';
        }

        $media = trim(str_replace('\\', '/', (string) ($post['data']['media']['path'] ?? '')), '/');
        $candidates = [];
        if ($media !== '') $candidates[] = self::PROJECTS_DIR . $media . '/' . $relative;
        $candidates[] = self::PROJECTS_DIR . $relative;

        foreach ($candidates as $candidate) {
            if (self::exists($candidate)) return $candidate;
        }
        return '';
    }

    private static function exists(string $path): bool
    {
        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';
        return is_file($root . $path);
    }
}
