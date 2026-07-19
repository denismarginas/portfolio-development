<?php

class Image
{
    public function render(array $data = []): string
    {
        $src = $data['src'] ?? '';
        $alt = $data['alt'] ?? '';
        $class = $data['class'] ?? 'responsive-image';
        $title = $data['title'] ?? '';
        $lazy = !empty($data['lazy']) ? 'loading="lazy"' : '';
        $additionalAttributes = $data['additionalAttributes'] ?? [];

        if (empty($src)) {
            return '';
        }

        $urlPath = $GLOBALS['urlPath'] ?? '';
        $fileSrc = ($urlPath !== '' && str_starts_with($src, $urlPath)) ? substr($src, strlen($urlPath)) : $src;
        $srcCurrent = __DIR__ . '/../../../../' . ltrim($fileSrc, '/\\');
        if (!file_exists($srcCurrent)) {
            return '<span> Image file not found: ' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . ' </span>';
        }

        $imageInfo = getimagesize($srcCurrent);
        if ($imageInfo === false) {
            return '<span> Failed to get image size for: ' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . ' </span>';
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if (empty($alt)) {
            $imagePathParts = pathinfo($fileSrc);
            $alt = isset($imagePathParts['filename']) ? 'Image: ' . $imagePathParts['filename'] : '';
        }

        $outputSrc = $src;
        if ($urlPath !== '' && !str_starts_with($src, 'http') && !str_starts_with($src, '//') && !str_starts_with($src, '/') && !str_starts_with($src, $urlPath)) {
            $outputSrc = $urlPath . $src;
        }

        $attributesPortion = ' ' . $lazy;
        foreach ($additionalAttributes as $attr => $value) {
            $attributesPortion .= ' ' . htmlspecialchars($attr, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace(
            ['{{ image_class }}', '{{ src }}', '{{ width }}', '{{ height }}', '{{ alt }}', '{{ title }}', '{{ attributes_portion }}'],
            [
                htmlspecialchars($class, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($outputSrc, ENT_QUOTES, 'UTF-8'),
                $width,
                $height,
                htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                $attributesPortion,
            ],
            $template
        );
    }
}
