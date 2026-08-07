<?php
$actions = '<button class="platform-button platform-button-ghost" type="button" data-platform-action="reload">' . PlatformSvg::render(['name' => 'update', 'size' => 16]) . htmlspecialchars($get('actions.reload'), ENT_QUOTES, 'UTF-8') . '</button>'
    . '<button class="platform-button platform-button-ghost" type="button" onclick="window.location.href=\'?page=edit-posts\'">' . PlatformSvg::render(['name' => 'edit', 'size' => 16]) . htmlspecialchars($get('actions.editPosts'), ENT_QUOTES, 'UTF-8') . '</button>'
    . '<button class="platform-button" type="button" data-platform-action="save">' . PlatformSvg::render(['name' => 'save', 'size' => 16]) . htmlspecialchars($get('actions.saveGraph'), ENT_QUOTES, 'UTF-8') . '</button>';

echo platform_render_topbar(['actions' => $actions]);
