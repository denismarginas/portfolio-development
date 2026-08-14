<?php

if (!defined('ENGINE_PROJECT_ROOT')) {
    define('ENGINE_PROJECT_ROOT', dirname(__DIR__, 3));
}

function platform_get_content_types(): ?array
{
    $typesPath = ENGINE_PROJECT_ROOT . '/src/content/json/data/settings/data_settings_types.json';
    if (!file_exists($typesPath)) {
        return null;
    }
    return json_decode(file_get_contents($typesPath), true);
}

function platform_get_post_types(): array
{
    $types = platform_get_content_types();
    if (!$types || empty($types['post'])) {
        return [];
    }
    $result = [];
    foreach ($types['post'] as $typeKey => $config) {
        $result[] = [
            'post_type' => $typeKey,
            'file' => $config['file'],
            'title' => $config['label'],
            'root' => $config['root'] ?? '',
            'structure' => $config['structure'] ?? '',
            'seo' => $config['seo'] ?? '',
            'routable' => $config['routable'] ?? true,
            'global_content_path' => $config['global_content_path'] ?? '',
            'global_img_path' => $config['global_img_path'] ?? '',
            'global_vid_path' => $config['global_vid_path'] ?? '',
        ];
    }
    return $result;
}

function platform_get_taxonomy_types(): array
{
    $types = platform_get_content_types();
    if (!$types || empty($types['taxonomy'])) {
        return [];
    }
    $result = [];
    foreach ($types['taxonomy'] as $typeKey => $config) {
        $result[] = [
            'taxonomy' => $typeKey,
            'file' => $config['file'],
            'title' => $config['label'],
            'root' => $config['root'] ?? '',
            'routable' => $config['routable'] ?? true,
            'post_types' => $config['post_types'] ?? [],
        ];
    }
    return $result;
}

function platform_get_item_types(): array
{
    $types = platform_get_content_types();
    if (!$types || empty($types['item'])) {
        return [];
    }
    $result = [];
    foreach ($types['item'] as $typeKey => $config) {
        $result[] = [
            'item_type' => $typeKey,
            'file' => $config['file'],
            'title' => $config['label'],
            'routable' => $config['routable'] ?? false,
        ];
    }
    return $result;
}

function platform_default_data_dir(): string
{
    $path = ENGINE_PROJECT_ROOT . '/src/content/json/data/settings/data_settings_languages.json';
    $default = 'en';
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        $default = $data['default'] ?? 'en';
    }
    return ENGINE_PROJECT_ROOT . '/src/content/json/data/' . $default;
}

function platform_find_data_file(string $filename): string
{
    $baseDir = platform_default_data_dir();
    $direct = $baseDir . '/' . $filename;
    if (file_exists($direct)) return $direct;
    foreach (glob($baseDir . '/*', GLOB_ONLYDIR) as $subDir) {
        $candidate = $subDir . '/' . $filename;
        if (file_exists($candidate)) return $candidate;
    }
    return $direct;
}

function platform_read_data_file(string $filename): ?array
{
    $path = platform_find_data_file($filename);
    if (!file_exists($path)) return null;
    $content = file_get_contents($path);
    if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
        $content = substr($content, 3);
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : null;
}

function platform_write_data_file(string $filename, array $data): bool
{
    $path = platform_find_data_file($filename);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}

function platform_find_post_in_file(string $filename, string $id): ?array
{
    $items = platform_read_data_file($filename);
    if (!$items) return null;
    foreach ($items as $i => $item) {
        if (($item['_id'] ?? '') === $id) {
            return ['index' => $i, 'data' => $item, 'file' => $filename];
        }
    }
    return null;
}

function platform_find_taxonomy_term(string $filename, string $id): ?array
{
    $terms = platform_read_data_file($filename);
    if (!$terms) return null;
    foreach ($terms as $i => $term) {
        if (($term['_id'] ?? '') === $id) {
            return ['index' => $i, 'data' => $term, 'file' => $filename];
        }
    }
    return null;
}