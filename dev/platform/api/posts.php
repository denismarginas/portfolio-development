<?php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../core/autoload.php';

if (!defined('ENGINE_PROJECT_ROOT')) {
    define('ENGINE_PROJECT_ROOT', dirname(__DIR__, 3));
}

function getPostTypes(): array
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
            $resolvedVars = platform_data::resolveCardVariables($card['variables'] ?? []);
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

function readDataFile(string $filename): ?array
{
    $path = ENGINE_PROJECT_ROOT . '/src/content/json/data/' . $filename;
    if (!file_exists($path)) return null;
    $content = file_get_contents($path);
    if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
        $content = substr($content, 3);
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : null;
}

function writeDataFile(string $filename, array $data): bool
{
    $path = ENGINE_PROJECT_ROOT . '/src/content/json/data/' . $filename;
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return file_put_contents($path, $json, LOCK_EX) !== false;
}

function findPostInFile(string $filename, string $postId): ?array
{
    $items = readDataFile($filename);
    if (!$items) return null;
    foreach ($items as $i => $item) {
        if (($item['post_id'] ?? '') === $postId) {
            return ['index' => $i, 'data' => $item, 'file' => $filename];
        }
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $postId = $_GET['post_id'] ?? '';
    $postType = $_GET['post_type'] ?? '';

    if ($postId) {
        // Find a specific post across all files
        $types = getPostTypes();
        foreach ($types as $t) {
            $found = findPostInFile($t['file'], $postId);
            if ($found) {
                echo json_encode([
                    'ok' => true,
                    'post' => $found['data'],
                    'file' => $found['file'],
                    'post_type' => $t['post_type'],
                    'global_content_path' => $t['global_content_path'] ?? '',
                    'global_img_path' => $t['global_img_path'] ?? '',
                    'global_vid_path' => $t['global_vid_path'] ?? '',
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit;
            }
        }
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Post not found']);
        exit;
    }

    if ($postType) {
        // List posts of a specific type
        $types = getPostTypes();
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
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Post type not found']);
            exit;
        }
        $items = readDataFile($file);
        if (!$items) {
            echo json_encode(['ok' => true, 'posts' => [], 'post_type' => $postType, 'global_content_path' => $globalContentPath, 'global_img_path' => $globalImgPath, 'global_vid_path' => $globalVidPath]);
            exit;
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
        echo json_encode(['ok' => true, 'posts' => $list, 'post_type' => $postType, 'file' => $file, 'global_content_path' => $globalContentPath, 'global_img_path' => $globalImgPath, 'global_vid_path' => $globalVidPath], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // List all post types with counts
    $types = getPostTypes();
    $result = [];
    foreach ($types as $t) {
        $items = readDataFile($t['file']);
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
    echo json_encode(['ok' => true, 'types' => $result], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['post_id']) || empty($input['file']) || !isset($input['data'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Missing post_id, file, or data']);
        exit;
    }

    $items = readDataFile($input['file']);
    if (!$items) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Data file not found']);
        exit;
    }

    $lookupId = $input['original_post_id'] ?? $input['post_id'];

    $found = false;
    foreach ($items as $i => &$item) {
        if (($item['post_id'] ?? '') === $lookupId) {
            $items[$i] = $input['data'];
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Post not found in file']);
        exit;
    }

    if (writeDataFile($input['file'], $items)) {
        echo json_encode(['ok' => true, 'message' => 'Post saved']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Failed to write file']);
    }
    exit;
}

if ($method === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['post_id']) || empty($input['file'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Missing post_id or file']);
        exit;
    }

    $items = readDataFile($input['file']);
    if (!$items) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Data file not found']);
        exit;
    }

    $found = false;
    foreach ($items as $i => $item) {
        if (($item['post_id'] ?? '') === $input['post_id']) {
            array_splice($items, $i, 1);
            $found = true;
            break;
        }
    }

    if (!$found) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Post not found']);
        exit;
    }

    if (writeDataFile($input['file'], $items)) {
        echo json_encode(['ok' => true, 'message' => 'Post deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Failed to write file']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
