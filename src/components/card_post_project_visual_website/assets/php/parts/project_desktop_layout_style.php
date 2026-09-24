<?php

/**
 * Reads the screenshot size and returns its placement classes:
 *   position: "top" | "center"
 *   bg:       "bg-primary fade-under" (tall, ratio <= 0.7) | "bg-white"
 */
class project_desktop_layout_style
{
    /** @return array{position:string, bg:string, aspect_ratio:?float, classes:string} */
    public static function resolve(string $type, string $screenPath): array
    {
        $position = 'top';
        $bg = '';
        $aspectRatio = null;

        $size = self::image_size($screenPath);
        if ($size !== null) {
            [$width, $height] = $size;
            $aspectRatio = $width / $height;

            if ($type === 'phone' && $width >= $height) {
                $position = 'center';
            } elseif ($type === 'desktop' && ($height > $width || ($aspectRatio >= 1 && $aspectRatio <= 1.6))) {
                $position = 'top';
            } else {
                $position = $aspectRatio >= 0.7 ? 'center' : 'top';
            }

            $bg = $aspectRatio <= 0.7 ? 'bg-primary fade-under' : 'bg-white';
        }

        return [
            'position' => $position,
            'bg' => $bg,
            'aspect_ratio' => $aspectRatio !== null ? round($aspectRatio, 2) : null,
            'classes' => trim($position . ' ' . $bg),
        ];
    }

    /** @return array{0:int, 1:int}|null */
    private static function image_size(string $screenPath): ?array
    {
        if ($screenPath === '') {
            return null;
        }

        $raw = ltrim($screenPath, '/');
        $absolute = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT . '/' . $raw : $raw;
        if (!file_exists($absolute)) {
            return null;
        }

        $info = @getimagesize($absolute);
        return ($info !== false && $info[1] > 0) ? [$info[0], $info[1]] : null;
    }
}
