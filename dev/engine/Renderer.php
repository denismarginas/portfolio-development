<?php

class Renderer
{
    public static function generate_all(string $url_path = null): array
    {
        $results = [];
        $dist_dir = EngineConfig::get('dist_dir');

        if (!is_dir($dist_dir)) {
            mkdir($dist_dir, 0755, true);
        }

        $valid_posts = DataService::get_valid_post_files();

        foreach ($valid_posts as $post_file) {
            $post_name = $post_file['name'];
            $posts = DataService::get_all_posts_from_file($post_name);

            if (empty($posts)) {
                $results[] = [
                    'file'   => $post_name,
                    'status' => 'skipped',
                    'reason' => 'No posts found',
                ];
                continue;
            }

            foreach ($posts as $post) {
                $result = self::render_single_post($post_name, $post, $dist_dir, $url_path);
                if ($result) {
                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    public static function render_single_post(string $post_name, array $post, string $output_dir = null, string $url_path = null): ?array
    {
        if ($output_dir === null) {
            $output_dir = EngineConfig::get('dist_dir');
        }

        $post_id = $post['post_id'] ?? '';
        if (empty($post_id)) return null;

        // Check if post should be rendered
        $settings = $post['settings'] ?? [];
        if (isset($settings['render']) && $settings['render'] === false) {
            return [
                'file'   => $post_id,
                'status' => 'skipped',
                'reason' => 'render: false in settings',
            ];
        }

        $seo = $post['seo'] ?? [];
        $slug = $seo['slug'] ?? $post_id;

        // Temporarily override url_path if provided
        $orig_url_path = $GLOBALS['url_path'] ?? '';
        if ($url_path !== null) {
            $GLOBALS['url_path'] = $url_path;
            $GLOBALS['urlPath'] = $url_path;
        }

        $html = self::render_post_html($post_name, $post);

        // Restore original
        if ($url_path !== null) {
            $GLOBALS['url_path'] = $orig_url_path;
            $GLOBALS['urlPath'] = $orig_url_path;
        }

        // Fix content paths: data files store paths without src/ prefix
        $url_prefix = $url_path ?? $orig_url_path;
        $html = str_replace(
            $url_prefix . 'content/',
            $url_prefix . 'src/content/',
            $html
        );

        if (empty($html)) {
            return [
                'file'   => $post_id,
                'status' => 'failed',
                'reason' => 'Empty HTML rendered',
            ];
        }

        $filename = $slug . '.html';
        $filepath = $output_dir . '/' . $filename;

        file_put_contents($filepath, $html);

        return [
            'file'     => $filename,
            'post_id'  => $post_id,
            'slug'     => $slug,
            'path'     => $filepath,
            'status'   => 'generated',
            'size'     => strlen($html),
        ];
    }

    public static function render_post_html(string $post_name, array $post): string
    {
        $content_sections = $post['content'] ?? [];
        $body_content = '';

        foreach ($content_sections as $section) {
            $component_name = $section['component'] ?? '';
            $component_data = $section['data'] ?? [];

            if (empty($component_name)) continue;

            switch ($post_name) {
                case 'projects':
                case 'workstations':
                    $merged_data = array_merge($post['data'] ?? [], $component_data);
                    $merged_data['post_current_data'] = $post['data'] ?? [];
                    $merged_data['post_id'] = $post['post_id'] ?? '';
                    break;
                default:
                    $merged_data = array_merge($post, $component_data);
                    $merged_data['post_current_data'] = $post;
                    break;
            }

            $section_html = ComponentRenderer::render($component_name, $merged_data);

            if (!str_contains($section_html, '<!-- Component not found')
                && !str_contains($section_html, '<!-- No PHP files')
                && !str_contains($section_html, '<!-- Component class not found')) {
                $body_content .= $section_html;
            }
        }

        $seo = $post['seo'] ?? [];

        $page_data = [
            'body_content' => $body_content,
            'seo'          => $seo,
            'content'      => $body_content,
        ];

        return ComponentRenderer::render('page_structure', $page_data);
    }

    public static function generate_post_index(string $post_name, array $posts = null): ?array
    {
        if ($posts === null) {
            $posts = DataService::get_all_posts_from_file($post_name);
        }

        if (empty($posts)) return null;

        $index = [];
        foreach ($posts as $post) {
            $seo = $post['seo'] ?? [];
            $slug = $seo['slug'] ?? $post['post_id'] ?? '';
            $render_settings = $post['settings']['render'] ?? true;

            if ($render_settings === false) continue;

            // Render each post to extract content summary
            $html = self::render_post_html($post_name, $post);

            // Extract meta info
            $title = $seo['title'] ?? '';
            $description = $seo['description'] ?? '';
            $default_img = $post['data']['media']['thumbnail'] ?? '';

            $index[] = [
                'page'             => $slug . '.html',
                'meta_title'       => $title,
                'meta_description' => $description,
                'post_type'        => $post_name,
                'content'          => strip_tags($html),
                'default_img'      => $default_img,
            ];
        }

        return $index;
    }

    public static function load_scss_config(): array
    {
        $config_path = EngineConfig::get('dev_dir') . '/compile/scss/scss-config.json';
        if (!file_exists($config_path)) {
            return ['clean_css_dir' => true, 'entries' => []];
        }
        return json_decode(file_get_contents($config_path), true) ?? [];
    }

    public static function compile_scss(string $filter_key = null): array
    {
        require_once EngineConfig::get('dev_dir') . '/compile/scss/compile.php';
        return ScssCompileManager::compile($filter_key);
    }
}
