<?php

function render_workflow_ui(): string
{
    $get = static function (string $path): string {
        return platform_data::getString($path);
    };

    ob_start();
    ?>
    <div class="platform-shell" data-platform-root data-api-url="api/cards.php" data-strings-url="data/strings.json">
        <?php require __DIR__ . '/parts/topbar.php'; ?>
        <main class="platform-workspace">
            <?php require __DIR__ . '/parts/sidebar-left.php'; ?>
            <?php require __DIR__ . '/parts/canvas.php'; ?>
            <?php require __DIR__ . '/parts/sidebar-right.php'; ?>
        </main>
    </div>
    <?php
    return ob_get_clean();
}
