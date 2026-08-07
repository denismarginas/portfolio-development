<?php

function platform_render_debug_page(string $pageStructureName, array $structureData, array $postData, string $globalContentPath, string $globalImgPath, string $globalVidPath): void
{
    $debugHtml = '<pre style="padding:20px;background:#141414;color:#47cbe3;overflow:auto;white-space:pre-wrap;font-size:12px;">'
               . htmlspecialchars(json_encode($postData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
               . '</pre>';
    echo PlatformComponentRenderer::render($pageStructureName, array_merge($structureData, [
        'body_content' => $debugHtml,
        'global_content_path' => $globalContentPath,
        'global_img_path' => $globalImgPath,
        'global_vid_path' => $globalVidPath,
        'seo' => $postData['seo'] ?? [],
        'post_current_data' => $postData,
    ]));
}

function platform_render_body_sections(array $postData, string $globalContentPath, string $globalImgPath, string $globalVidPath): string
{
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

        $bodyHtml .= PlatformComponentRenderer::render(str_replace('-', '_', $componentName), $componentData);
    }

    return $bodyHtml;
}

function platform_wrap_body_if_needed(string $bodyHtml, array $postData, ?string $bodyWrapper): string
{
    if ($bodyWrapper && $bodyHtml !== '' && isset($postData['data'])) {
        $wrapperData = array_merge($postData['data'], [
            'postContent' => $bodyHtml,
            'postCurrentData' => $postData['data'],
            'post_current_data' => $postData['data'],
        ]);
        return PlatformComponentRenderer::render($bodyWrapper, $wrapperData);
    }
    return $bodyHtml;
}

function platform_write_dist_html(array $pageData, string $pageStructureName, string $postId, string $postType, bool $htmlCompileFolder): void
{
    $globalSettings = PlatformDataService::get_data('settings_routing');
    $extension = $globalSettings['routing']['extension'] ?? $globalSettings['page_slug_extension'] ?? '.html';

    $relPath = ($htmlCompileFolder && $postType !== 'page' ? $postType . '/' : '') . $postId . $extension;

    $GLOBALS['render_target'] = 'dist';
    $GLOBALS['dist_rel_path'] = $relPath;

    $postData = $pageData['post_current_data'] ?? [];
    if (!empty($postData)) {
        $pageData['body_content'] = platform_render_body_sections(
            $postData,
            $pageData['global_content_path'] ?? '',
            $pageData['global_img_path'] ?? '',
            $pageData['global_vid_path'] ?? ''
        );
    }

    $distHtml = PlatformComponentRenderer::render($pageStructureName, $pageData);

    $distAbsPath = ENGINE_PROJECT_ROOT . '/dist/' . $relPath;
    if (!is_dir(dirname($distAbsPath))) {
        mkdir(dirname($distAbsPath), 0777, true);
    }
    file_put_contents($distAbsPath, $distHtml);
}
