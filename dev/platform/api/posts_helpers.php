<?php

function platform_get_post_types(): array
{
    $cardsPath = __DIR__ . '/../data/cards.json';
    if (!file_exists($cardsPath)) {
        return [];
    }
    $graph = json_decode(file_get_contents($cardsPath), true);
    if (!$graph || empty($graph['cards'])) {
        return [];
    }
    $types = [];
    foreach ($graph['cards'] as $card) {
        if ($card['type'] === 'selectfile') {
            $file = '';
            $postType = '';
            $globalContentPath = '';
            $globalImgPath = '';
            $globalVidPath = '';
            $resolvedVars = PlatformData::resolveCardVariables($card['variables'] ?? []);
            foreach ($resolvedVars as $var) {
                if ($var['name'] === 'file') $file = $var['value'];
                if ($var['name'] === 'post_type') $postType = $var['value'];
                if ($var['name'] === 'global_content_path') $globalContentPath = $var['value'];
                if ($var['name'] === 'global_img_path') $globalImgPath = $var['value'];
                if ($var['name'] === 'global_vid_path') $globalVidPath = $var['value'];
            }
            if ($file && $postType) {
                $types[] = [
                    'post_type' => $postType,
                    'file' => $file,
                    'title' => $card['title'],
                    'global_content_path' => $globalContentPath,
                    'global_img_path' => $globalImgPath,
                    'global_vid_path' => $globalVidPath,
                ];
            }
        }
    }
    return $types;
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

function platform_find_post_in_file(string $filename, string $postId): ?array
{
    $items = platform_read_data_file($filename);
    if (!$items) return null;
    foreach ($items as $i => $item) {
        if (($item['post_id'] ?? '') === $postId) {
            return ['index' => $i, 'data' => $item, 'file' => $filename];
        }
    }
    return null;
}
