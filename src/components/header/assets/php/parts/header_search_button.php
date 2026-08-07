<?php

trait header_search_button
{
    protected static function render_search_button(array $data = []): string
    {
        $mappings = PlatformConfig::getRoutingMappings();
        $searchId = $mappings['search_post_id'] ?? 'search';
        $searchUrl = PlatformPathService::post_link($searchId);

        $currentPostId = $data['post_current_data']['post_id'] ?? '';
        $isActive = $currentPostId !== '' && $searchId === $currentPostId;

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_search_button.html', [
            'active_class' => $isActive ? 'active' : '',
            'aria_current' => $isActive ? 'aria-current="page"' : '',
            'search_url' => htmlspecialchars($searchUrl, ENT_QUOTES, 'UTF-8'),
            'search_icon_html' => PlatformComponentRenderer::render('svg', [
                'icon' => 'search',
                'class' => 'header-search-button-icon',
            ]),
        ]);
    }
}
