<?php

require_once __DIR__ . '/posts_helpers.php';

function platform_handle_post_save(): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['post_id']) || empty($input['file']) || !isset($input['data'])) {
        platform_send_json(['ok' => false, 'message' => 'Missing post_id, file, or data'], 400);
        return;
    }

    $items = platform_read_data_file($input['file']);
    if (!$items) {
        platform_send_json(['ok' => false, 'message' => 'Data file not found'], 404);
        return;
    }

    $lookupId = $input['original_post_id'] ?? $input['post_id'];

    $found = false;
    foreach ($items as $i => $item) {
        if (($item['post_id'] ?? '') === $lookupId) {
            $items[$i] = $input['data'];
            $found = true;
            break;
        }
    }

    if (!$found) {
        platform_send_json(['ok' => false, 'message' => 'Post not found in file'], 404);
        return;
    }

    if (platform_write_data_file($input['file'], $items)) {
        platform_send_json(['ok' => true, 'message' => 'Post saved']);
    } else {
        platform_send_json(['ok' => false, 'message' => 'Failed to write file'], 500);
    }
}

function platform_handle_post_delete(): void
{
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['post_id']) || empty($input['file'])) {
        platform_send_json(['ok' => false, 'message' => 'Missing post_id or file'], 400);
        return;
    }

    $items = platform_read_data_file($input['file']);
    if (!$items) {
        platform_send_json(['ok' => false, 'message' => 'Data file not found'], 404);
        return;
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
        platform_send_json(['ok' => false, 'message' => 'Post not found'], 404);
        return;
    }

    if (platform_write_data_file($input['file'], $items)) {
        platform_send_json(['ok' => true, 'message' => 'Post deleted']);
    } else {
        platform_send_json(['ok' => false, 'message' => 'Failed to write file'], 500);
    }
}
