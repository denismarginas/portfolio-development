<?php

function platform_render_cards(array $cards): string
{
    if ($cards === []) return '';

    platform_asset('css', 'components/cards/assets/css/cards.css');

    $html = '<div class="platform-cards-grid">';

    foreach ($cards as $card) {
        $title  = $card['title'] ?? '';
        $desc   = $card['desc'] ?? '';
        $icon   = $card['icon'] ?? '';
        $url    = $card['url'] ?? '';
        $action = $card['action'] ?? '';
        $class  = $card['class'] ?? '';
        $active = !empty($card['active']);

        $inner = '<span class="platform-cards-item-icon">' . PlatformSvg::render(['name' => $icon, 'size' => 18, 'class' => 'platform-cards-item-icon-svg']) . '</span>'
            . '<span class="platform-cards-item-copy">'
            . '<span class="platform-cards-item-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</span>'
            . ($desc !== '' ? '<span class="platform-cards-item-desc">' . htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') . '</span>' : '')
            . '</span>';

        $cls = 'platform-cards-item' . ($active ? ' is-active' : '') . ($class !== '' ? ' ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') : '');

        if ($action !== '') {
            $html .= '<button class="' . $cls . '" type="button" data-platform-card-action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '">' . $inner . '</button>';
        } else {
            $html .= '<a class="' . $cls . '" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $inner . '</a>';
        }
    }

    $html .= '</div>';
    return $html;
}
