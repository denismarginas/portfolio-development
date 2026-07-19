<?php

/**
 * Dev Server - Live PHP preview
 * Usage: php -S localhost:8000 dev/server.php
 * Then visit: http://localhost:8000/?post_id=home
 */

require_once __DIR__ . '/engine/bootstrap.php';

// Serve static files from src/ or dist/
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = ltrim($uri, '/');

// Check for static files in src/ (images, videos, etc.)
$src_file = $root . '/src/' . $uri;
if (!empty($uri) && file_exists($src_file) && !is_dir($src_file)) {
    $ext = strtolower(pathinfo($src_file, PATHINFO_EXTENSION));
    $mime_types = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
        'ico'  => 'image/x-icon',
        'webm' => 'video/webm',
        'mp4'  => 'video/mp4',
        'json' => 'application/json',
    ];

    $mime = $mime_types[$ext] ?? 'application/octet-stream';
    header('Content-Type: ' . $mime);
    readfile($src_file);
    return true;
}

// Get post_id from query string
$post_id = $_GET['post_id'] ?? '';

// If no post_id specified, show a listing
if (empty($post_id)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Portfolio Engine - Dev Server</title>';
    echo '<style>body{font-family:system-ui,sans-serif;max-width:800px;margin:2em auto;padding:0 1em}';
    echo 'h1{color:#333}a{color:#0066cc;text-decoration:none}a:hover{text-decoration:underline}';
    echo 'ul{list-style:none;padding:0}li{padding:.3em 0}.info{color:#666;font-size:.9em}</style>';
    echo '</head><body>';
    echo '<h1>Portfolio Engine - Dev Server</h1>';
    echo '<p class="info">Running. Use <code>?post_id=home</code> to preview a page.</p>';
    echo '<h2>Available pages:</h2><ul>';

    $valid_posts = DataService::get_valid_post_files();
    foreach ($valid_posts as $pf) {
        $posts = DataService::get_all_posts_from_file($pf['name']);
        if (empty($posts)) continue;
        foreach ($posts as $post) {
            $seo = $post['seo'] ?? [];
            $id = $post['post_id'] ?? '';
            $title = $seo['title'] ?? $id;
            $slug = $seo['slug'] ?? $id;
            $post_type = $pf['name'];
            $settings = $post['settings'] ?? [];
            if (isset($settings['render']) && $settings['render'] === false) continue;
            echo '<li><a href="/?post_id=' . htmlspecialchars($id) . '">' . htmlspecialchars($title) . '</a>';
            echo ' <span class="info">(' . htmlspecialchars($slug) . ' — ' . htmlspecialchars($post_type) . ')</span></li>';
        }
    }

    echo '</ul>';
    echo '<h2>Actions:</h2>';
    echo '<p><a href="/?action=compile_scss" style="background:#0066cc;color:#fff;padding:8px 16px;border-radius:4px;display:inline-block;">Compile SCSS</a>';
    echo ' <a href="/?action=generate" style="background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;display:inline-block;">Generate Static HTML</a></p>';
    echo '</body></html>';
    return true;
}

// Handle actions
$action = $_GET['action'] ?? '';
if (!empty($action)) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Action: ' . htmlspecialchars($action) . '</title>';
    echo '<style>body{font-family:system-ui,sans-serif;max-width:800px;margin:2em auto;padding:0 1em}';
    echo 'pre{background:#f4f4f4;padding:1em;border-radius:4px;overflow-x:auto}</style></head><body>';

    if ($action === 'compile_scss') {
        echo '<h1>SCSS Compilation Result</h1><pre>';
        $result = Renderer::compile_scss();
        print_r($result);
        echo '</pre>';
    } elseif ($action === 'generate') {
        echo '<h1>Static HTML Generation Result</h1><pre>';
        $results = Renderer::generate_all();
        foreach ($results as $r) {
            echo htmlspecialchars($r['status'] . ': ' . ($r['file'] ?? $r['post_id'] ?? '?')) . "\n";
            if (!empty($r['path'])) echo '  -> ' . htmlspecialchars($r['path']) . "\n";
            if (!empty($r['size'])) echo '  size: ' . $r['size'] . " bytes\n";
            if (!empty($r['reason'])) echo '  reason: ' . htmlspecialchars($r['reason']) . "\n";
        }
        echo '</pre>';
    } else {
        echo '<h1>Unknown action</h1>';
    }

    echo '<p><a href="/">Back</a></p></body></html>';
    return true;
}

// Render a specific post
header('Content-Type: text/html; charset=utf-8');

// Find which post file contains this post_id
$post_data = null;
$post_name = null;

$valid_posts = DataService::get_valid_post_files();
foreach ($valid_posts as $pf) {
    $found = DataService::get_post_by_id($pf['name'], $post_id);
    if ($found !== null) {
        $post_data = $found;
        $post_name = $pf['name'];
        break;
    }
}

if ($post_data === null) {
    echo '<!DOCTYPE html><html><head><title>404 - Post not found</title></head><body>';
    echo '<h1>Post not found</h1>';
    echo '<p>No post with ID: ' . htmlspecialchars($post_id) . '</p>';
    echo '<p><a href="/">Back to index</a></p>';
    echo '</body></html>';
    return true;
}

// Render the post
$html = Renderer::render_post_html($post_name, $post_data);
echo $html;
return true;
