<?php

require_once __DIR__ . '/engine-bridge.php';
require_once __DIR__ . '/../core/autoload.php';

header('Content-Type: text/html; charset=UTF-8');

$postId = isset($_GET['post_id']) ? trim($_GET['post_id']) : '';

if (!$postId) {
    http_response_code(400);
    echo '<h1>Missing post_id parameter</h1>';
    exit;
}

// Read workflow graph
$cardsPath = ENGINE_PROJECT_ROOT . '/dev/platform/data/cards.json';
$debugMode = false;
$compileAssets = false;
$htmlCompile = false;
$htmlCompileFolder = false;
$structureVars = [];
$seoVars = [];
$globalPaths = [];

if (file_exists($cardsPath)) {
    $graph = json_decode(file_get_contents($cardsPath), true);
    if ($graph && !empty($graph['cards'])) {
        foreach ($graph['cards'] as $card) {
            $type = $card['type'] ?? '';
            $resolvedVars = platform_data::resolveCardVariables($card['variables'] ?? []);
            foreach ($resolvedVars as $var) {
                if ($type === 'project_structure' || $type === 'page_structure') {
                    $structureVars[$type][$var['name']] = $var['value'];
                } elseif ($type === 'seo_project' || $type === 'seo_page') {
                    $seoVars[$type][$var['name']] = $var['value'];
                } elseif ($type === 'render') {
                    if ($var['name'] === 'debug_post_data' && $var['value'] === 'true') {
                        $debugMode = true;
                    }
                    if ($var['name'] === 'compile_assets') {
                        $compileAssets = $var['value'] === 'true';
                    }
                    if ($var['name'] === 'html_compile') {
                        $htmlCompile = $var['value'] === 'true';
                    }
                    if ($var['name'] === 'html_compile_folder') {
                        $htmlCompileFolder = $var['value'] === 'true';
                    }
                } elseif ($type === 'selectfile') {
                    $postTypeVar = '';
                    $globalVars = [];
                    foreach ($resolvedVars as $cv) {
                        if ($cv['name'] === 'post_type') { $postTypeVar = $cv['value']; }
                        elseif (str_starts_with($cv['name'], 'global_')) { $globalVars[$cv['name']] = $cv['value']; }
                    }
                    if ($postTypeVar) {
                        $globalPaths[$postTypeVar] = $globalVars;
                    }
                }
            }
        }
    }
}

// URL override: ?debug_post_data=true
if (isset($_GET['debug_post_data']) && $_GET['debug_post_data'] === 'true') {
    $debugMode = true;
}

// Search all data files for the post_id
$postData = null;
$postType = '';

$validPostNames = [
    'projects' => 'project',
    'pages' => 'page',
    'workstations' => 'workstation',
];

foreach ($validPostNames as $name => $type) {
    $posts = data_service::get_all_posts_from_file($name);
    if ($posts === null) continue;
    foreach ($posts as $post) {
        if (($post['post_id'] ?? '') === $postId) {
            $postData = $post;
            $postType = $type;
            break 2;
        }
    }
}

if (!$postData) {
    http_response_code(404);
    echo '<h1>Post not found: ' . htmlspecialchars($postId) . '</h1>';
    exit;
}

// Normalize taxonomy (categories/tags now live under data.taxonomy)
if (isset($postData['data']['taxonomy'])) {
    $postData['data'] = array_merge($postData['data']['taxonomy'], $postData['data']);
}

// Normalize SEO from data.seo to top-level seo (pages now have seo inside data)
if (!isset($postData['seo']) && isset($postData['data']['seo'])) {
    $postData['seo'] = $postData['data']['seo'];
}

// Pick the right structure and SEO config based on post type
$structureKey = $postType === 'project' ? 'project_structure' : 'page_structure';
$seoKey = $postType === 'project' ? 'seo_project' : 'seo_page';

$headerComponent = $structureVars[$structureKey]['header'] ?? null;
$footerComponent = $structureVars[$structureKey]['footer'] ?? null;
$pageStructureComponent = $structureVars[$structureKey]['page_structure'] ?? null;
$bodyWrapper = $structureVars[$structureKey]['body_wrapper'] ?? null;
$seoConfig = $seoVars[$seoKey] ?? [];

// Auto-generate SEO only for project-type posts (pages have it built-in)
if ($postType === 'project' && !isset($postData['seo'])) {
    $titleSrc = $postData['data']['title'] ?? $postId;
    $descSrc = $postData['data']['description'] ?? '';
    $titleMax = isset($seoConfig['title_max']) ? (int)$seoConfig['title_max'] : 50;
    $descMax = isset($seoConfig['description_max']) ? (int)$seoConfig['description_max'] : 140;

    $postData['seo'] = [
        'title' => strlen($titleSrc) > $titleMax ? substr($titleSrc, 0, $titleMax - 3) . '...' : $titleSrc,
        'description' => strlen($descSrc) > $descMax ? substr($descSrc, 0, $descMax - 3) . '...' : $descSrc,
        'keywords' => $seoConfig['keywords_source'] ?? $postId,
        'index' => ($seoConfig['index'] ?? 'index') === 'index',
        'slug' => $postData['seo']['slug'] ?? $postData['post_id'] ?? '',
    ];
}

// Build structure config
// Resolve global paths for this post type
$postGlobalPaths = $globalPaths[$postType] ?? [];
$globalContentPath = $postGlobalPaths['global_content_path'] ?? '';
$globalImgPath = $postGlobalPaths['global_img_path'] ?? '';
$globalVidPath = $postGlobalPaths['global_vid_path'] ?? '';

$structureData = [
    'header_component' => $headerComponent,
    'footer_component' => $footerComponent,
    'global_content_path' => $globalContentPath,
    'global_img_path' => $globalImgPath,
    'global_vid_path' => $globalVidPath,
    'compile_assets' => $compileAssets,
];
$pageStructureName = $pageStructureComponent ?: 'page_constructor';
$seo = $postData['seo'] ?? [];

if ($debugMode) {
    $debugHtml = '<pre style="padding:20px;background:#141414;color:#47cbe3;overflow:auto;white-space:pre-wrap;font-size:12px;">'
               . htmlspecialchars(json_encode($postData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
               . '</pre>';
    echo render_component($pageStructureName, array_merge($structureData, [
        'body_content' => $debugHtml,
        'global_content_path' => $globalContentPath,
        'global_img_path' => $globalImgPath,
        'global_vid_path' => $globalVidPath,
        'seo' => $seo,
        'post_current_data' => $postData,
    ]));
    exit;
}

// Render content sections
$bodyHtml = '';
$contentItems = $postData['data']['content'] ?? $postData['content'] ?? [];

foreach ($contentItems as $item) {
    $componentName = $item['component'] ?? '';
    if (!$componentName) continue;

    $componentData = array_merge($item['data'] ?? [], [
        'post_current_data' => $postData,
        'global_content_path' => $globalContentPath,
        'global_img_path' => $globalImgPath,
        'global_vid_path' => $globalVidPath,
        'children' => $item['children'] ?? [],
    ]);

    $html = render_component(str_replace('-', '_', $componentName), $componentData);
    $bodyHtml .= $html;
}

// Apply body wrapper only for posts with nested data (projects/workstations)
if ($bodyWrapper && $bodyHtml !== '' && isset($postData['data'])) {
    $wrapperData = array_merge($postData['data'], [
        'postContent' => $bodyHtml,
        'postCurrentData' => $postData['data'],
        'post_current_data' => $postData['data'],
    ]);
    $bodyHtml = render_component($bodyWrapper, $wrapperData);
}

$pageData = array_merge($structureData, [
    'body_content' => $bodyHtml,
    'global_content_path' => $globalContentPath,
    'global_img_path' => $globalImgPath,
    'global_vid_path' => $globalVidPath,
    'seo' => $seo,
    'post_current_data' => $postData,
]);

$GLOBALS['render_target'] = 'preview';
echo render_component($pageStructureName, $pageData);

if ($htmlCompile) {
    $globalSettings = get_data_json('data_global_settings', 'data');
    $extension = $globalSettings['page_slug_extension'] ?? '.html';

    $relPath = ($htmlCompileFolder && $postType !== 'page' ? $postType . '/' : '') . $postId . $extension;

    $GLOBALS['render_target'] = 'dist';
    $GLOBALS['dist_rel_path'] = $relPath;
    $distHtml = render_component($pageStructureName, $pageData);

    $distAbsPath = ENGINE_PROJECT_ROOT . '/dist/' . $relPath;
    if (!is_dir(dirname($distAbsPath))) {
        mkdir(dirname($distAbsPath), 0777, true);
    }
    file_put_contents($distAbsPath, $distHtml);
}
