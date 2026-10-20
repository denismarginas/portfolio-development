<?php

/**
 *   PlatformComponentRenderer::render('block_workstation_item_card', [
 *       'product' => [ "name" => "...", "tag" => "GPU", "img_src" => "gpu.webp", "description" => "Label: value\n..." ],
 *       'image_dir' => 'src/content/img/workstation/pc-01/',
 *       'id' => 'configuration-gpu', 'group' => 'popup group',
 *   ]);
 *
 * data:
 *   product    object  name (required), tag, img_src (file in image_dir or a "src/..." path), description
 *   image_dir  string  folder of img_src
 *   id         string  anchor id
 *   group      string  popup gallery group (cards of one listing open as one gallery)
 *   class      string  optional
 */
class block_workstation_item_card
{
    private const PLACEHOLDER = 'placeholder/img-placeholder.webp';

    public static function render(array $data = []): string
    {
        $product = is_array($data['product'] ?? null) ? $data['product'] : $data;
        $name = self::str($product['name'] ?? '');
        if ($name === '') return '';

        $file = self::str($product['img_src'] ?? '');
        $dir = self::str($data['image_dir'] ?? '');
        $src = ($file === '' || str_contains($file, '..')) ? '' : (str_starts_with($file, 'src/') ? $file : ($dir !== '' ? rtrim($dir, '/') . '/' . ltrim($file, '/') : ''));

        $image = $src !== '' ? (string) PlatformComponentRenderer::render('image', ['src' => $src, 'alt' => $name, 'class' => 'block-workstation-item-card-img', 'lazy' => true]) : '';
        $hasImage = trim($image) !== '';
        if (!$hasImage) {
            $image = (string) PlatformComponentRenderer::render('image', ['src' => self::PLACEHOLDER, 'alt' => '', 'class' => 'block-workstation-item-card-img', 'lazy' => true]);
        }

        $tag = self::str($product['tag'] ?? '');
        $group = self::str($data['group'] ?? '');
        $class = self::str($data['class'] ?? '');

        return PlatformTemplateRenderer::render([
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id' => self::e(self::str($data['id'] ?? '') ?: trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-')),
            'tag_attr' => $tag !== '' ? ' data-tag="' . self::e(strtolower($tag)) . '"' : '',
            'popup_attrs' => $hasImage ? ' data-popup="true"' . ($group !== '' ? ' data-popup-group="' . self::e($group) . '"' : '') : '',
            'image' => $image,
            'tag' => $tag !== '' ? '<span class="block-workstation-item-card-tag">' . self::e($tag) . '</span>' : '',
            'name' => self::e($name),
            'specs' => self::specs(self::str($product['description'] ?? '')),
        ]);
    }

    /** "Label: value" lines -> <dl> in the hover panel; lines without ":" as full rows. */
    private static function specs(string $text): string
    {
        $rows = '';
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $pos = strpos($line, ':');
            $rows .= ($pos === false || $pos === 0)
                ? '<div class="block-workstation-item-card-spec"><dd>' . self::e($line) . '</dd></div>'
                : '<div class="block-workstation-item-card-spec"><dt>' . self::e(trim(substr($line, 0, $pos))) . '</dt><dd>' . self::e(trim(substr($line, $pos + 1))) . '</dd></div>';
        }
        return $rows !== '' ? '<div class="block-workstation-item-card-hover"><dl class="block-workstation-item-card-specs">' . $rows . '</dl></div>' : '';
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
