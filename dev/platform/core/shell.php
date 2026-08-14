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
    $get = static function (string $path, string $default = ''): string {
        return PlatformData::getString($path, $default);
    };

    return [
        'home'            => ['label' => $get('home.title', 'Dashboard'), 'icon' => 'home', 'url' => '?page=home', 'desc' => $get('home.description', 'Build, render and publish the portfolio.')],
        'structure-types' => [
            'label'    => $get('structureTypes.label', 'Structure Types'),
            'icon'     => 'edit',
            'url'      => '?page=structure-types',
            'desc'     => $get('structureTypes.label', 'Structure Types'),
            'children' => [
                'posts'      => ['label' => $get('structureTypes.posts', 'Posts'), 'icon' => 'web', 'url' => '?page=edit-posts', 'desc' => $get('structureTypes.postsDesc', 'Manage pages, projects and workstations.')],
                'taxonomies' => ['label' => $get('structureTypes.taxonomies', 'Taxonomies'), 'icon' => 'tag', 'url' => '?page=edit-taxonomies', 'desc' => $get('structureTypes.taxonomiesDesc', 'Manage categories and taxonomies.')],
                'items'      => ['label' => $get('structureTypes.items', 'Items'), 'icon' => 'database', 'url' => '?page=edit-items', 'desc' => $get('structureTypes.itemsDesc', 'Manage data items (jobs, education, games, socials).')],
            ],
        ],
        'render'          => [
            'label'    => $get('actions.compileScss', 'Render'),
            'icon'     => 'render',
            'url'      => '?page=render&tab=compile',
            'desc'     => 'Compile stylesheets and preview rendered posts.',
            'children' => [
                'compile'      => ['label' => $get('actions.compileScss', 'Compile SCSS'), 'icon' => 'compile', 'url' => '?page=render&tab=compile', 'desc' => 'Build CSS from component stylesheets.'],
                'preview'      => ['label' => $get('actions.view', 'Live Preview'), 'icon' => 'view', 'url' => '?page=render&tab=preview', 'desc' => 'Preview any post rendered by the page constructor.'],
                'render-html'  => ['label' => $get('actions.translate', 'HTML Bulk Render'), 'icon' => 'html', 'url' => '?page=render&tab=render-html', 'desc' => 'Render every post to static HTML files.'],
            ],
        ],
        'generate'        => [
            'label'    => $get('actions.translate', 'Generate'),
            'icon'     => 'generate',
            'url'      => '?page=generate&tab=translate',
            'desc'     => 'Generate site artifacts from content data.',
            'children' => [
                'translate' => ['label' => $get('actions.translate', 'Translate'), 'icon' => 'update', 'url' => '?page=generate&tab=translate', 'desc' => 'Synchronise content data across languages.'],
            ],
        ],
        'settings'        => ['label' => $get('home.settings', 'Settings'), 'icon' => 'settings', 'url' => '?page=settings', 'desc' => $get('home.settingsDesc', 'Site, routing, languages and SEO configuration.')],
        'workflow'        => ['label' => $get('title', 'Workflow'), 'icon' => 'sitemap', 'url' => '?page=workflow', 'desc' => 'Build and run content pipelines.'],
    ];
}

function platform_pages(): array
{
    $get = static function (string $path, string $default = ''): string {
        return PlatformData::getString($path, $default);
    };

    return [
        'home'           => ['title' => $get('home.title', 'Dashboard'), 'render' => 'platform_render_home_fragment', 'file' => 'components/home/index.php'],
        'structure-types'=> ['title' => $get('structureTypes.label', 'Structure Types'), 'render' => 'platform_render_structure_types_fragment', 'file' => 'components/structure-types/index.php'],
        'edit-posts'     => ['title' => $get('editPosts.pageTitle', 'Post Editor'), 'render' => 'platform_render_edit_posts_fragment', 'file' => 'components/edit-posts/index.php'],
        'edit-taxonomies'=> ['title' => $get('structureTypes.taxonomies', 'Taxonomies'), 'render' => 'platform_render_edit_taxonomies_fragment', 'file' => 'components/edit-taxonomies/index.php'],
        'edit-items'     => ['title' => $get('structureTypes.items', 'Items'), 'render' => 'platform_render_edit_items_fragment', 'file' => 'components/edit-items/index.php'],
        'taxonomies'     => ['title' => $get('structureTypes.taxonomies', 'Taxonomies'), 'render' => 'platform_render_taxonomies_fragment', 'file' => 'components/taxonomies/index.php'],
        'items'          => ['title' => $get('structureTypes.items', 'Items'), 'render' => 'platform_render_items_fragment', 'file' => 'components/items/index.php'],
        'settings'       => ['title' => $get('home.settings', 'Settings'), 'render' => 'platform_render_settings_fragment', 'file' => 'components/settings/index.php'],
        'render'         => ['title' => $get('actions.compileScss', 'Render'), 'render' => 'platform_render_render_fragment', 'file' => 'components/render/index.php'],
        'generate'       => ['title' => $get('actions.translate', 'Generate'), 'render' => 'platform_render_generate_fragment', 'file' => 'components/generate/index.php'],
        'workflow'       => ['title' => $get('title', 'Workflow'), 'render' => 'platform_render_workflow_fragment', 'file' => 'components/workflow-ui/index.php'],
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