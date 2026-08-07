<?php

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../core/autoload.php';
require_once __DIR__ . '/../preview/preview_load.php';
require_once __DIR__ . '/../preview/preview_post.php';
require_once __DIR__ . '/../preview/preview_render.php';
require_once __DIR__ . '/posts_helpers.php';

function platform_send_json(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

$state = platform_load_preview_state();

if (!$state['html_compile']) {
    platform_send_json(['ok' => true, 'html_compile' => false, 'message' => 'html_compile flag is off in the Render card.', 'count' => 0, 'results' => []]);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    platform_send_json(['ok' => false, 'message' => 'Use POST'], 405);
    exit;
}

$scssFlags = $state['compile_scss_flags'];
if (!empty($scssFlags['compile_scss_everytime'])) {
    PlatformScssBuilder::run($scssFlags);
}

$results = [];
$count = 0;

$types = platform_get_post_types();

foreach ($types as $t) {
    $postType = $t['post_type'];
    $structureKey = $postType === 'project' ? 'project_structure' : 'page_structure';
    $seoKey = $postType === 'project' ? 'seo_project' : 'seo_page';

    $headerComponent = $state['structure_vars'][$structureKey]['header'] ?? null;
    $footerComponent = $state['structure_vars'][$structureKey]['footer'] ?? null;
    $pageStructureComponent = $state['structure_vars'][$structureKey]['page_structure'] ?? null;
    $bodyWrapper = $state['structure_vars'][$structureKey]['body_wrapper'] ?? null;
    $seoConfig = $state['seo_vars'][$seoKey] ?? [];

    $posts = platform_read_data_file($t['file']);
    if (!$posts) continue;

    foreach ($posts as $post) {
        $postId = $post['post_id'] ?? '';
        if (!$postId) continue;

        try {
            $postData = platform_normalize_preview_post($post);
            if ($postType === 'project') {
                $postData = platform_generate_project_seo($postData, $seoConfig, $postId);
            }

            $postGlobalPaths = $state['global_paths'][$postType] ?? [];
            $globalContentPath = $postGlobalPaths['global_content_path'] ?? '';
            $globalImgPath = $postGlobalPaths['global_img_path'] ?? '';
            $globalVidPath = $postGlobalPaths['global_vid_path'] ?? '';

            $structureData = [
                'header_component' => $headerComponent,
                'footer_component' => $footerComponent,
                'global_content_path' => $globalContentPath,
                'global_img_path' => $globalImgPath,
                'global_vid_path' => $globalVidPath,
                'compile_assets' => $state['compile_assets'],
            ];
            $pageStructureName = $pageStructureComponent ?: 'page_constructor';
            $seo = $postData['seo'] ?? [];

            $bodyHtml = platform_render_body_sections($postData, $globalContentPath, $globalImgPath, $globalVidPath);
            $bodyHtml = platform_wrap_body_if_needed($bodyHtml, $postData, $bodyWrapper);

            $pageData = array_merge($structureData, [
                'body_content' => $bodyHtml,
                'global_content_path' => $globalContentPath,
                'global_img_path' => $globalImgPath,
                'global_vid_path' => $globalVidPath,
                'seo' => $seo,
                'post_current_data' => $postData,
            ]);

            platform_write_dist_html($pageData, $pageStructureName, $postId, $postType, $state['html_compile_folder']);
            $results[] = ['post_id' => $postId, 'post_type' => $postType, 'success' => true];
            $count++;
        } catch (Throwable $e) {
            $results[] = ['post_id' => $postId, 'post_type' => $postType, 'success' => false, 'error' => $e->getMessage()];
        }
    }
}

platform_send_json(['ok' => true, 'html_compile' => true, 'count' => $count, 'results' => $results]);
