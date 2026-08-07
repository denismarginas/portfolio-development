<?php

function platform_render_workflow_fragment(string $tab = ''): string
{
    platform_asset('css', 'components/workflow-ui/assets/css/style.css');

    platform_asset('js', 'assets/js/parts/platform-view.js');
    platform_asset('js', 'assets/js/parts/platform-canvas.js');
    platform_asset('js', 'components/workflow-ui/assets/js/workflow-ui.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-utils.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-render-cards.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-render-links.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-inspector.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-actions.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-drag.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-files.js');
    platform_asset('js', 'components/workflow-ui/assets/js/parts/workflow-ui-templates.js');

    $get = static function (string $path): string {
        return PlatformData::getString($path);
    };

    ob_start();
    ?>
    <div class="platform-workspace" data-platform-root data-api-url="api/cards.php" data-strings-url="data/strings.json">
        <div class="platform-workflow-toolbar">
            <button class="platform-button platform-button-ghost" type="button" data-platform-action="reload"><?php echo PlatformSvg::render(['name' => 'update', 'size' => 16]); ?><?php echo htmlspecialchars($get('actions.reload'), ENT_QUOTES, 'UTF-8'); ?></button>
            <a class="platform-button platform-button-ghost" href="?page=edit-posts"><?php echo PlatformSvg::render(['name' => 'edit', 'size' => 16]); ?><?php echo htmlspecialchars($get('actions.editPosts'), ENT_QUOTES, 'UTF-8'); ?></a>
            <button class="platform-button" type="button" data-platform-action="save"><?php echo PlatformSvg::render(['name' => 'save', 'size' => 16]); ?><?php echo htmlspecialchars($get('actions.saveGraph'), ENT_QUOTES, 'UTF-8'); ?></button>
        </div>
        <?php require __DIR__ . '/parts/sidebar-left.php'; ?>
        <?php require __DIR__ . '/parts/canvas.php'; ?>
        <?php require __DIR__ . '/parts/sidebar-right.php'; ?>
    </div>
    <?php
    return ob_get_clean();
}
