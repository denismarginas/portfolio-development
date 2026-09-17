<?php

/**
 * project_desktop_layout_style
 *
 * Ported from the legacy post-project-functions.php aspect-ratio check
 * (the block that computed $layoutAtr via getimagesize()). Inspects the
 * actual screenshot file on disk and works out whether it should be
 * anchored "top" or "center" inside its device screen, plus a couple of
 * background utility classes and the raw aspect ratio.
 *
 * Legacy:
 *   $layoutAtr = "cover top";
 *   if (!empty($img)) {
 *       $src_current = __DIR__ . "/../../../" . $img;
 *       if (file_exists($src_current)) {
 *           $imageInfo = getimagesize($src_current);
 *           if ($imageInfo !== false) {
 *               [$imageWidth, $imageHeight] = $imageInfo;
 *               $aspectRatio = $imageWidth / $imageHeight;
 *               if ($type == "phone" && $imageWidth >= $imageHeight) {
 *                   $layoutAtr = "center";
 *               } elseif ($type == "desktop" && ($imageHeight > $imageWidth || ($aspectRatio >= 1 && $aspectRatio <= 1.6))) {
 *                   $layoutAtr = "top";
 *               } else {
 *                   $layoutAtr = ($aspectRatio >= 0.7) ? "center" : "top";
 *               }
 *               $layoutAtr .= ($aspectRatio <= 0.7) ? " bg-primary fade-under" : " bg-white";
 *               $layoutAtr .= " " . round($aspectRatio, 2);
 *           }
 *       }
 *   }
 *
 * Path resolution was updated for the new engine: screenshot paths coming
 * out of card_post_project_visual_website already look like
 * "src/content/img/projects/.../web_desktop_overview.webp", so they're
 * resolved against ENGINE_PROJECT_ROOT the same way image_render.php does,
 * instead of the old relative "__DIR__ . /../../../" walk.
 */
class project_desktop_layout_style
{
    /**
     * @param string $type       "desktop" or "phone"
     * @param string $screenPath screenshot path as produced by
     *                           card_post_project_visual_website::resolve_screens()
     *                           (e.g. "src/content/img/projects/.../web_desktop_overview.webp")
     * @return array{position:string, bg:string, aspect_ratio:?float, classes:string}
     */
    public static function resolve(string $type, string $screenPath): array
    {
        $position = 'top';
        $bg = '';
        $aspectRatio = null;

        if ($screenPath !== '') {
            $raw = ltrim($screenPath, '/');
            $absolute = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT . '/' . $raw : $raw;

            if (file_exists($absolute)) {
                $imageInfo = @getimagesize($absolute);
                if ($imageInfo !== false && $imageInfo[1] > 0) {
                    $imageWidth = $imageInfo[0];
                    $imageHeight = $imageInfo[1];
                    $aspectRatio = $imageWidth / $imageHeight;

                    if ($type === 'phone' && $imageWidth >= $imageHeight) {
                        $position = 'center';
                    } elseif ($type === 'desktop' && ($imageHeight > $imageWidth || ($aspectRatio >= 1 && $aspectRatio <= 1.6))) {
                        $position = 'top';
                    } else {
                        $position = ($aspectRatio >= 0.7) ? 'center' : 'top';
                    }

                    $bg = ($aspectRatio <= 0.7) ? 'bg-primary fade-under' : 'bg-white';
                }
            }
        }

        $classes = trim($position . ' ' . $bg);

        return [
            'position' => $position,
            'bg' => $bg,
            'aspect_ratio' => $aspectRatio !== null ? round($aspectRatio, 2) : null,
            'classes' => $classes,
        ];
    }
}
