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
 *   img              string  or: image path relative to src/content/img/ (or a full "src/..." path),
 *                            e.g. "personal-images/dm-working-in-office.webp" (pages, no post media folder)
 *   alt              string  alt text (default: post title)
 *   caption          string  optional caption under the image
 *   popup_group      string  optional data-popup-group, to browse several blocks in one popup gallery
 *   class            string  optional extra class
 *   id               string  optional id
 *   card             bool    outlined card with padding (default true); false = just the rounded image
 *   popup            bool    opens in the popup on click (default true)
 *   cover            bool    image fills the block and is cropped (object-fit: cover), default false
 *   width            string  CSS width of the block, e.g. "100%", "480px" (default: natural)
 *   height           string  CSS height, e.g. "100%" (fills the column), "360px"
 *   height_lg / height_md / height_sm / height_xs
 *                    string  height on smaller screens (max-width: lg 1024, md 768, sm 480, xs 320),
 *                            e.g. "height_md": "320px", "height_sm": "220px"
 *   Sizes accept px, %, rem, em, vh, vw or "auto".
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
        [$sizeClasses, $style] = self::sizes($data);
        $card = ($data['card'] ?? true) !== false;
        $popup = ($data['popup'] ?? true) !== false;
        $group = trim((string) ($data['popup_group'] ?? ''));
        $extra = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render([
            'card_class' => $card ? ' card-outline' : ' block-image-plain',
            'extra_class' => ($sizeClasses !== '' ? ' ' . $sizeClasses : '') . ($extra !== '' ? ' ' . htmlspecialchars($extra, ENT_QUOTES, 'UTF-8') : ''),
            'popup_attr' => $popup ? ' data-popup="true"' : '',
            'style_attr' => $style !== '' ? ' style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '"' : '',
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

    /** [ classes, style ] for cover / width / height(_lg|_md|_sm|_xs). */
    private static function sizes(array $data): array
    {
        $classes = [];
        $vars = [];
        $map = [
            'width' => '--block-image-width',
            'height' => '--block-image-height',
            'height_lg' => '--block-image-height-lg',
            'height_md' => '--block-image-height-md',
            'height_sm' => '--block-image-height-sm',
            'height_xs' => '--block-image-height-xs',
        ];
        foreach ($map as $key => $var) {
            $value = is_scalar($data[$key] ?? null) ? strtolower(trim((string) $data[$key])) : '';
            if ($value === '' || !preg_match('/^(auto|\d+(\.\d+)?(px|%|rem|em|vh|vw))$/', $value)) continue;
            $vars[] = $var . ': ' . $value;
        }
        if ($vars) $classes[] = 'block-image-sized';
        if (($data['cover'] ?? false) === true) $classes[] = 'block-image-cover';
        if (is_scalar($data['height'] ?? null) && trim((string) $data['height']) === '100%') $classes[] = 'block-image-fill';

        return [implode(' ', $classes), $vars ? implode('; ', $vars) . ';' : ''];
    }

    /** Project-relative path of the image, or "" when it can't be found. */
    private static function source(array $data, array $post): string
    {
        $img = is_scalar($data['img'] ?? null) ? trim(str_replace('\\', '/', (string) $data['img']), '/') : '';
        if ($img !== '' && !str_contains($img, '..')) {
            $candidate = str_starts_with($img, 'src/') ? $img : 'src/content/img/' . $img;
            return self::exists($candidate) ? $candidate : '';
        }

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
