<?php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../core/autoload.php';
require_once __DIR__ . '/posts_helpers.php';

if (!defined('ENGINE_PROJECT_ROOT')) {
    define('ENGINE_PROJECT_ROOT', dirname(__DIR__, 3));
}

function platform_send_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

function platform_handle_get_post(string $postId): void
{
    $types = platform_get_post_types();
    foreach ($types as $t) {
        $found = platform_find_post_in_file($t['file'], $postId);
        if ($found) {
            platform_send_json([
                'ok' => true,
                'post' => $found['data'],
                'file' => $found['file'],
                'post_type' => $t['post_type'],
                'global_content_path' => $t['global_content_path'] ?? '',
                'global_img_path' => $t['global_img_path'] ?? '',
                'global_vid_path' => $t['global_vid_path'] ?? '',
            ]);
            return;
        }
    }
    platform_send_json(['ok' => false, 'message' => 'Post not found'], 404);
}

function platform_handle_list_posts(string $postType): void
{
    $types = platform_get_post_types();
    $file = '';
    $globalContentPath = '';
    $globalImgPath = '';
    $globalVidPath = '';
    foreach ($types as $t) {
        if ($t['post_type'] === $postType) {
            $file = $t['file'];
            $globalContentPath = $t['global_content_path'] ?? '';
            $globalImgPath = $t['global_img_path'] ?? '';
            $globalVidPath = $t['global_vid_path'] ?? '';
            break;
        }
    }
    if (!$file) {
        platform_send_json(['ok' => false, 'message' => 'Post type not found'], 404);
        return;
    }
    $items = platform_read_data_file($file);
    if (!$items) {
        platform_send_json(['ok' => true, 'posts' => [], 'post_type' => $postType, 'global_content_path' => $globalContentPath, 'global_img_path' => $globalImgPath, 'global_vid_path' => $globalVidPath]);
        return;
    }
    $list = [];
    foreach ($items as $item) {
        $title = '';
        if (isset($item['data']['title'])) $title = $item['data']['title'];
        elseif (isset($item['title'])) $title = $item['title'];
        elseif (isset($item['data']['seo']['title'])) $title = $item['data']['seo']['title'];
        elseif (isset($item['seo']['title'])) $title = $item['seo']['title'];
        $list[] = ['post_id' => $item['post_id'] ?? '', 'title' => $title];
    }
    platform_send_json(['ok' => true, 'posts' => $list, 'post_type' => $postType, 'file' => $file, 'global_content_path' => $globalContentPath, 'global_img_path' => $globalImgPath, 'global_vid_path' => $globalVidPath]);
}

function platform_handle_list_types(): void
{
    $types = platform_get_post_types();
    $result = [];
    foreach ($types as $t) {
        $items = platform_read_data_file($t['file']);
        $result[] = [
            'post_type' => $t['post_type'],
            'file' => $t['file'],
            'title' => $t['title'],
            'count' => $items ? count($items) : 0,
            'global_content_path' => $t['global_content_path'] ?? '',
            'global_img_path' => $t['global_img_path'] ?? '',
            'global_vid_path' => $t['global_vid_path'] ?? '',
        ];
    }
    platform_send_json(['ok' => true, 'types' => $result]);
}
