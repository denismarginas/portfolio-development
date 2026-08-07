<?php

trait post_items_image
{
    protected static function render_image_link(array $post, array $context, string $title, string $link): string
    {
        $mediaPath = (string)self::value_at($post, $context['media_path_param'] ?? ['data', 'media', 'path']);
        if ($mediaPath === '') return '';

        $dir = $context['global_img_path'] . '/' . $mediaPath . '/' . $context['img_path'];
        $imageFile = self::resolve_image_file($dir, $context['img_filename']);
        if ($imageFile === '') return '';

        $imageHtml = PlatformComponentRenderer::render('image', [
            'path' => $dir . $imageFile,
            'alt' => $title,
        ]);
        if ($imageHtml === '') return '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_item_image.html', [
            'url' => htmlspecialchars($link),
            'image' => $imageHtml,
        ]);
    }

    protected static function resolve_image_file(string $dir, string $base): string
    {
        $root = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT . '/' : '';
        $exact = $dir . $base . '.webp';
        if (file_exists($root . $exact)) return $base . '.webp';

        $matches = glob($root . $dir . $base . '-*', GLOB_NOSORT);
        if (!$matches) return '';
        $file = basename($matches[0]);
        return $file;
    }
}
