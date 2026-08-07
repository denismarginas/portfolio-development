<?php

if (!isset($GLOBALS['_platform_assets'])) {
    $GLOBALS['_platform_assets'] = ['css' => [], 'js' => []];
}

function platform_asset(string $type, string ...$paths): void
{
    foreach ($paths as $path) {
        if (!in_array($path, $GLOBALS['_platform_assets'][$type], true)) {
            $GLOBALS['_platform_assets'][$type][] = $path;
        }
    }
}

function platform_menu(): array
{
    return [
        'home'       => ['label' => 'Home',     'icon' => 'home',     'url' => '?page=home',         'desc' => 'Return to the dashboard overview.'],
        'edit-posts' => ['label' => 'Posts',    'icon' => 'edit',     'url' => '?page=edit-posts',   'desc' => 'Manage pages, projects and workstations.'],
        'render'     => [
            'label'    => 'Render',
            'icon'     => 'render',
            'url'      => '?page=render&tab=compile',
            'desc'     => 'Compile stylesheets and preview rendered posts.',
            'children' => [
                'compile' => ['label' => 'Compile SCSS', 'icon' => 'compile', 'url' => '?page=render&tab=compile', 'desc' => 'Build CSS from component stylesheets.'],
                'preview' => ['label' => 'Live Preview', 'icon' => 'view',    'url' => '?page=render&tab=preview', 'desc' => 'Preview any post rendered by the page constructor.'],
                'render-html' => ['label' => 'HTML Bulk Render', 'icon' => 'html', 'url' => '?page=render&tab=render-html', 'desc' => 'Render every post to static HTML files.'],
            ],
        ],
        'generate'   => [
            'label'    => 'Generate',
            'icon'     => 'generate',
            'url'      => '?page=generate&tab=translate',
            'desc'     => 'Generate site artifacts from content data.',
            'children' => [
                'translate' => ['label' => 'Translate', 'icon' => 'update', 'url' => '?page=generate&tab=translate', 'desc' => 'Synchronise content data across languages.'],
            ],
        ],
        'settings'   => ['label' => 'Settings', 'icon' => 'settings', 'url' => '?page=settings',   'desc' => 'Site, routing, languages and SEO configuration.'],
        'workflow'   => ['label' => 'Workflow', 'icon' => 'sitemap',      'url' => '?page=workflow',   'desc' => 'Build and run content pipelines.'],
    ];
}

function platform_pages(): array
{
    return [
        'home'       => ['title' => 'Home',     'render' => 'platform_render_home_fragment',       'file' => 'components/home/index.php'],
        'edit-posts' => ['title' => 'Posts',    'render' => 'platform_render_edit_posts_fragment', 'file' => 'components/edit-posts/index.php'],
        'settings'   => ['title' => 'Settings', 'render' => 'platform_render_settings_fragment',   'file' => 'components/settings/index.php'],
        'render'     => ['title' => 'Render',   'render' => 'platform_render_render_fragment',     'file' => 'components/render/index.php'],
        'generate'   => ['title' => 'Generate', 'render' => 'platform_render_generate_fragment',   'file' => 'components/generate/index.php'],
        'workflow'   => ['title' => 'Workflow', 'render' => 'platform_render_workflow_fragment',   'file' => 'components/workflow-ui/index.php'],
    ];
}

function platform_render_menu(array $menu, string $page, string $tab): string
{
    $html = '<nav class="platform-menu" aria-label="Main menu"><ul class="platform-menu-list">';

    foreach ($menu as $key => $item) {
        $hasChildren = !empty($item['children']);

        if ($hasChildren) {
            $html .= '<li class="platform-menu-group">';
            $html .= '<a class="platform-menu-link platform-menu-group-link" href="' . htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') . '">'
                . PlatformSvg::render(['name' => $item['icon'], 'size' => 16, 'class' => 'platform-menu-icon'])
                . '<span class="platform-menu-label">' . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</span>'
                . '</a>';
            $html .= '<ul class="platform-menu-children">';
            foreach ($item['children'] as $childKey => $child) {
                $isActive = $page === $key && $tab === $childKey;
                $html .= '<li><a class="platform-menu-link platform-menu-child-link' . ($isActive ? ' is-active' : '') . '" href="' . htmlspecialchars($child['url'], ENT_QUOTES, 'UTF-8') . '">'
                    . PlatformSvg::render(['name' => $child['icon'], 'size' => 14, 'class' => 'platform-menu-icon'])
                    . '<span class="platform-menu-label">' . htmlspecialchars($child['label'], ENT_QUOTES, 'UTF-8') . '</span>'
                    . '</a></li>';
            }
            $html .= '</ul>';
            $html .= '</li>';
            continue;
        }

        $isActive = $page === $key;
        $html .= '<li><a class="platform-menu-link' . ($isActive ? ' is-active' : '') . '" href="' . htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') . '">'
            . PlatformSvg::render(['name' => $item['icon'], 'size' => 16, 'class' => 'platform-menu-icon'])
            . '<span class="platform-menu-label">' . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . '</span>'
            . '</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
