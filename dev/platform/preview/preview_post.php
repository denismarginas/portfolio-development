<?php

function platform_find_preview_post(string $postId): array
{
    $validPostNames = [
        'projects' => 'project',
        'pages' => 'page',
        'workstations' => 'workstation',
    ];

    foreach ($validPostNames as $name => $type) {
        $posts = PlatformDataService::get_all_posts_from_file($name);
        if ($posts === null) continue;
        foreach ($posts as $post) {
            if (($post['post_id'] ?? '') === $postId) {
                return ['post' => $post, 'type' => $type];
            }
        }
    }

    return ['post' => null, 'type' => ''];
}

function platform_normalize_preview_post(array $postData): array
{
    if (isset($postData['data']['taxonomy'])) {
        $postData['data'] = array_merge($postData['data']['taxonomy'], $postData['data']);
    }

    if (!isset($postData['seo']) && isset($postData['data']['seo'])) {
        $postData['seo'] = $postData['data']['seo'];
    }

    return $postData;
}

function platform_generate_project_seo(array $postData, array $seoConfig, string $postId): array
{
    if (isset($postData['seo'])) return $postData;

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

    return $postData;
}
