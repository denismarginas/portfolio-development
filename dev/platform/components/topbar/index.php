<?php

function platform_render_topbar(array $data = []): string
{
    $strings = PlatformData::getStrings();
    $topbar = $strings['topbar'] ?? [];

    $title = $data['title'] ?? $topbar['title'] ?? 'Portfolio Builder';
    $subtitle = $data['subtitle'] ?? $topbar['subtitle'] ?? '';
    $logo = $data['logo'] ?? $topbar['logo'] ?? ['svg' => 'web'];
    $logoName = is_array($logo) ? ($logo['svg'] ?? $logo['name'] ?? '') : '';

    $logoHtml = '';
    if ($logoName !== '') {
        $logoHtml = PlatformSvg::render(['name' => $logoName, 'size' => 22, 'class' => 'platform-topbar-logo-icon']);
    }

    $actions = $data['actions'] ?? '';

    return '<div class="platform-topbar">'
        . '<div class="platform-topbar-logo">' . $logoHtml . '</div>'
        . '<div class="platform-topbar-copy">'
        . '<h1 class="platform-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h1>'
        . ($subtitle !== '' ? '<p class="platform-subtitle">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</p>' : '')
        . '</div>'
        . '<div class="platform-topbar-actions">' . $actions . '</div>'
        . '</div>';
}
