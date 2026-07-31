<?php

class helpers
{
    public static function get_images_in_folder(string $dir_path): array
    {
        if (!is_dir($dir_path)) return [];

        $images = [];
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'];

        $files = scandir($dir_path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_extensions)) {
                $images[] = $file;
            }
        }

        sort($images);
        return $images;
    }

    public static function get_directories_in_folder(string $dir_path): array
    {
        if (!is_dir($dir_path)) return [];

        $dirs = [];
        $items = scandir($dir_path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (is_dir($dir_path . '/' . $item)) {
                $dirs[] = $item;
            }
        }

        return $dirs;
    }

    public static function list_design(int $count): string
    {
        if ($count <= 1) return 'single';
        if ($count <= 3) return 'double';
        if ($count <= 6) return 'triple';
        return 'grid';
    }

    public static function change_space_with_hyphen_and_lowercase(string $str): string
    {
        return strtolower(str_replace(' ', '-', trim($str)));
    }

    public static function remove_space_and_lowercase(string $str): string
    {
        return strtolower(preg_replace('/\s+/', '', $str));
    }

    public static function add_https(string $url): string
    {
        return url_service::add_https($url);
    }

    public static function remove_https(string $url): string
    {
        return url_service::remove_https($url);
    }

    public static function execute_php_in_string(string $str): string
    {
        $age = '';
        $last_update = '';
        if (str_contains($str, '{{age}}') || str_contains($str, '{{last-update-date}}') || str_contains($str, '{{last_update_date}}')) {
            $birth_date = '1997-09-09';
            $age = date_diff(date_create($birth_date), date_create('today'))->y;
            $last_update = date('d.m.Y');
        }

        $str = str_replace(['{{age}}', '{{last-update-date}}', '{{last_update_date}}'], [$age, $last_update, $last_update], $str);

        return $str;
    }

    public static function check_echo(mixed $value): void
    {
        if (is_string($value) || is_numeric($value)) {
            echo htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }
    }

    public static function render_image(string $src, bool $lazy = false, string $class = '', bool $has_overlay = false, array $attributes = []): string
    {
        return image_renderer::render([
            'src' => $src,
            'lazy' => $lazy,
            'class' => $class,
            'additionalAttributes' => $attributes,
            'has_overlay' => $has_overlay,
        ]);
    }

    public static function render_bg_img_overlay_texture(): string
    {
        return '<div class="bg-texture"></div>';
    }

    public static function get_theme_path(): string
    {
        $global = data_service::get_global_settings();
        $url_path = engine_config::getUrlPath();
        $theme_dir = $global['themes_path'] ?? 'src/components/theme/assets';
        $active_dir = $global['theme_active']['dir_name'] ?? '';
        $path = $url_path . $theme_dir;
        if (!empty($active_dir)) {
            $path .= '/' . $active_dir;
        }
        return $path;
    }

    public static function get_videos_in_folder(string $dir_path): array
    {
        if (!is_dir($dir_path)) return [];

        $videos = [];
        $allowed_extensions = ['mp4', 'webm', 'ogv', 'mov', 'avi', 'mkv'];

        $files = scandir($dir_path);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed_extensions)) {
                $videos[] = $file;
            }
        }

        sort($videos);
        return $videos;
    }
}
