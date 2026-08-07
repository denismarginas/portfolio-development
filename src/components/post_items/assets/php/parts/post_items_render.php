<?php

trait post_items_render
{
    public static function render(array $data = []): string
    {
        $postTypes = self::resolve_post_types($data['post_type'] ?? null);

        $posts = [];
        foreach ($postTypes as $postType) {
            $typePosts = PlatformDataService::get_all_posts_from_file($postType);
            if (empty($typePosts)) continue;
            foreach ($typePosts as $post) {
                $post['_post_type'] = $postType;
                $posts[] = $post;
            }
        }
        if (empty($posts)) return '';

        $template = $data['template'] ?? null;
        $excludeBy = $data['exclude_by'] ?? [];

        $items = '';
        foreach ($posts as $post) {
            if (($post['settings']['render'] ?? true) === false) continue;
            if (self::is_excluded($post, $excludeBy)) continue;

            if (is_array($template)) {
                $items .= self::render_via_template($post, $template);
            } else {
                $postType = (string) ($post['_post_type'] ?? 'projects');
                $items .= self::render_item($post, self::build_context($data, $postType));
            }
        }

        if ($items === '') return '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template.html', [
            'items' => $items,
        ]);
    }

    protected static function is_excluded(array $post, mixed $excludeBy): bool
    {
        $entries = is_array($excludeBy) ? $excludeBy : [$excludeBy];
        if (empty($entries)) return false;

        $context = [
            'post_id' => (string) ($post['post_id'] ?? ''),
            'data' => $post['data'] ?? [],
            'settings' => $post['settings'] ?? [],
            'img_base' => '',
        ];

        foreach ($entries as $entry) {
            if (is_string($entry)) {
                $path = $entry;
                $expected = null;
            } elseif (is_array($entry)) {
                $path = (string) ($entry['path'] ?? $entry['ref'] ?? '');
                $expected = $entry['value'] ?? null;
            } else {
                continue;
            }

            if ($path === '') continue;
            $resolved = self::resolve_token($path, $context);

            if ($expected !== null) {
                if (self::values_equal($resolved, $expected)) return true;
            } elseif (self::is_truthy($resolved)) {
                return true;
            }
        }

        return false;
    }

    protected static function values_equal(mixed $a, mixed $b): bool
    {
        if (is_bool($b)) {
            return (bool) $a === $b;
        }
        if (is_string($b) || is_int($b) || is_float($b)) {
            return (string) $a === (string) $b;
        }
        return $a === $b;
    }

    protected static function is_truthy(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === false || $value === 0 || $value === '0' || $value === 'false') {
            return false;
        }
        if (is_array($value)) {
            return !empty($value);
        }
        return true;
    }

    protected static function resolve_post_types(mixed $postType): array
    {
        if ($postType === null || $postType === '' || $postType === false || $postType === []) {
            $valid = PlatformDataService::get_valid_post_files();
            return array_values(array_map(static fn ($v) => $v['name'], $valid));
        }

        if (is_string($postType)) {
            return array_values(array_filter(array_map('trim', explode(',', $postType)), static fn ($v) => $v !== ''));
        }

        if (is_array($postType)) {
            return array_values($postType);
        }

        return ['projects'];
    }

    protected static function render_via_template(array $post, array $template): string
    {
        $component = (string) ($template['component'] ?? 'card');
        $rawParams = (array) ($template['params'] ?? $template['data'] ?? []);

        $postType = (string) ($post['_post_type'] ?? 'projects');
        $contentBase = rtrim((string) ($template['global_content_path'] ?? ''), '/');
        $imgBase = $contentBase !== ''
            ? $contentBase . '/img'
            : rtrim((string) ($template['global_img_path'] ?? 'src/content/img'), '/');
        $imgBase = rtrim($imgBase, '/');

        $params = array_merge($rawParams, [
            'post_id' => (string) ($post['post_id'] ?? ''),
        ]);

        $context = [
            'post_id' => (string) ($post['post_id'] ?? ''),
            'data' => $post['data'] ?? [],
            'img_base' => $imgBase . '/' . $postType,
        ];
        $params = self::resolve_template_value($params, $context);
        if (!is_array($params)) {
            $params = [];
        }
        $params['post_current_data'] = $post;

        $item = PlatformComponentRenderer::render($component, $params);

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_item.html', [
            'item' => $item,
        ]);
    }

    protected static function build_context(array $data, string $postType): array
    {
        $contentBase = rtrim((string)($data['global_content_path'] ?? ''), '/');
        $globalImgPath = $contentBase !== '' ? $contentBase . '/img/' . $postType : rtrim((string)($data['global_img_path'] ?? 'src/content/img/' . $postType), '/');

        return [
            'img_path' => rtrim((string)($data['feature_image_path'] ?? 'web/overview/'), '/') . '/',
            'img_filename' => (string)($data['feature_image_filename'] ?? 'web_desktop_overview'),
            'title_param' => $data['title'] ?? ['data', 'seo', 'title'],
            'media_path_param' => $data['media_path'] ?? ['data', 'media', 'path'],
            'global_img_path' => $globalImgPath,
        ];
    }
}
