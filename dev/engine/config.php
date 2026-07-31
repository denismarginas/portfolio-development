<?php

class engine_config
{
    private static array $config = [];
    private static bool $initialized = false;

    public static function init(): void
    {
        if (self::$initialized) return;

        $root_dir = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT : dirname(__DIR__, 2);

        self::$config = [
            'root_dir'            => $root_dir,
            'src_dir'             => $root_dir . '/src',
            'dist_dir'            => $root_dir . '/dist',
            'dev_dir'             => $root_dir . '/dev',
            'engine_dir'          => $root_dir . '/dev/engine',
            'theme_dir'           => $root_dir . '/src/components/theme',
            'components_dir'      => $root_dir . '/src/components',
            'data_dir'            => $root_dir . '/src/content/json/data',
            'index_dir'           => $root_dir . '/src/content/json/index',
            'theme_scss_dir'      => $root_dir . '/src/components/theme/assets/scss',
            'theme_css_dir'       => $root_dir . '/src/components/theme/assets/css',
            'theme_js_dir'        => $root_dir . '/src/components/theme/assets/js',
            'img_dir'             => $root_dir . '/src/content/img',
            'vid_dir'             => $root_dir . '/src/content/vid',
            'style_css_output'    => 'style',
            'components_css_output' => 'components',
        ];

        self::$initialized = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::init();
        return self::$config[$key] ?? $default;
    }

    public static function all(): array
    {
        self::init();
        return self::$config;
    }

    public static function getGlobalData(): ?array
    {
        return get_data_json('data_global_settings');
    }

    public static function getUrlPath(): string
    {
        return $GLOBALS['url_path'] ?? '';
    }

    public static function getPageSlugExtension(): string
    {
        $global = self::getGlobalData();
        return $global['page_slug_extension'] ?? '.html';
    }

    public static function getHtmlRenderPath(): string
    {
        $global = self::getGlobalData();
        return $global['theme_active']['html_render_path'] ?? 'dist';
    }
}
