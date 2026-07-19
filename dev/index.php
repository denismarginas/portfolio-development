<?php
require_once __DIR__ . '/engine/bootstrap.php';

// Override url_path to match the actual dev server location
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$dir_name = dirname($script_name);
$base_path = rtrim($dir_name, '/') . '/';
// If we're at /portfolio-engine/dev/, set url_path to /portfolio-engine/
$parent_dir = dirname($dir_name);
$GLOBALS['url_path'] = rtrim($parent_dir, '/') . '/';
$GLOBALS['urlPath'] = $GLOBALS['url_path'];

$post_id = $_GET['post_id'] ?? '';

if (empty($post_id)) {
    $action = $_GET['action'] ?? '';
    if (!empty($action)) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>Action: ' . htmlspecialchars($action) . '</title>';
        echo '<style>body{font-family:system-ui,sans-serif;max-width:800px;margin:2em auto;padding:0 1em}';
        echo 'pre{background:#f4f4f4;padding:1em;border-radius:4px;overflow-x:auto}</style></head><body>';

        if ($action === 'compile_scss') {
            $filter = $_GET['filter'] ?? null;
            echo '<h1>SCSS Compilation Result</h1><pre>';
            $result = Renderer::compile_scss($filter ?: null);
            print_r($result);
            echo '</pre>';
            echo '<p><a href="?">Back</a></p>';
            exit;
        } elseif ($action === 'generate') {
            echo '<h1>Static HTML Generation Result</h1><pre>';
            $results = Renderer::generate_all($GLOBALS['url_path']);
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
        echo '<p><a href="?">Back</a></p></body></html>';
        exit;
    }

    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Portfolio Engine - Dev Preview</title>';
    echo '<style>body{font-family:system-ui,sans-serif;max-width:800px;margin:2em auto;padding:0 1em}';
    echo 'h1{color:#333}a{color:#0066cc;text-decoration:none}a:hover{text-decoration:underline}';
    echo 'ul{list-style:none;padding:0}li{padding:.3em 0}.info{color:#666;font-size:.9em}</style>';
    echo '</head><body>';
    echo '<h1>Portfolio Engine - Dev Preview</h1>';
    echo '<p class="info">Add <code>?post_id=home</code> to preview a page.</p>';
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
            echo '<li><a href="?post_id=' . htmlspecialchars($id) . '">' . htmlspecialchars($title) . '</a>';
            echo ' <span class="info">(' . htmlspecialchars($slug) . ' &mdash; ' . htmlspecialchars($post_type) . ')</span></li>';
        }
    }

    echo '</ul>';
    echo '<h2>Actions:</h2>';
    echo '<p><a href="?action=compile_scss" style="background:#0066cc;color:#fff;padding:8px 16px;border-radius:4px;display:inline-block;">Compile All SCSS</a>';
    echo ' <a href="?action=generate" style="background:#28a745;color:#fff;padding:8px 16px;border-radius:4px;display:inline-block;">Generate Static HTML</a></p>';

    // SCSS compile form
    $scss_config = Renderer::load_scss_config();
    if (!empty($scss_config['entries'])) {
        echo '<h3>Selective SCSS Compile</h3>';
        echo '<form method="get" action="" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">';
        echo '<input type="hidden" name="action" value="compile_scss">';
        foreach ($scss_config['entries'] as $entry) {
            if (empty($entry['enabled'])) continue;
            $key = $entry['key'] ?? '';
            $label = $entry['label'] ?? $key;
            echo '<button type="submit" name="filter" value="' . htmlspecialchars($key) . '" ';
            echo 'style="background:#6c757d;color:#fff;padding:6px 12px;border:none;border-radius:4px;cursor:pointer">';
            echo htmlspecialchars($label) . '</button>';
        }
        echo '</form>';
    }

    echo '</body></html>';
    exit;
}

header('Content-Type: text/html; charset=utf-8');

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
    echo '<!DOCTYPE html><html><head><title>404 &mdash; Post not found</title></head><body>';
    echo '<h1>Post not found</h1>';
    echo '<p>No post with ID: ' . htmlspecialchars($post_id) . '</p>';
    echo '<p><a href="?">Back to index</a></p>';
    echo '</body></html>';
    exit;
}

$html = Renderer::render_post_html($post_name, $post_data);

// Fix content paths: data files store paths without src/ prefix
$url_prefix = $GLOBALS['url_path'];
$html = str_replace(
    $url_prefix . 'content/',
    $url_prefix . 'src/content/',
    $html
);

echo $html;
