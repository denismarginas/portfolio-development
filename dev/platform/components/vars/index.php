<?php

function platform_card_vars(string $cardType): array
{
    $cardsPath = __DIR__ . '/../../data/cards.json';
    if (!file_exists($cardsPath)) return [];
    $graph = json_decode(file_get_contents($cardsPath), true);
    foreach (($graph['cards'] ?? []) as $card) {
        if (($card['type'] ?? '') !== $cardType) continue;
        $out = [];
        foreach (($card['variables'] ?? []) as $var) {
            $out[] = ['name' => $var['name'] ?? '', 'value' => (string) ($var['value'] ?? '')];
        }
        return $out;
    }
    return [];
}

function platform_render_vars(string $cardType, string $action = ''): string
{
    platform_asset('css', 'components/vars/assets/css/vars.css');
    platform_asset('js', 'components/vars/assets/js/vars.js');

    $variables = platform_card_vars($cardType);

    if ($variables === []) return '';

    $html = '<div class="platform-vars" data-platform-vars data-vars-card-type="' . htmlspecialchars($cardType, ENT_QUOTES, 'UTF-8') . '"' . ($action !== '' ? ' data-vars-action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '"' : '') . '>';
    $html .= '<h3 class="platform-vars-title">Params</h3>';
    $html .= '<div class="platform-vars-flex">';

    foreach ($variables as $var) {
        $isBool = $var['value'] === 'true' || $var['value'] === 'false';
        $html .= '<label class="platform-field platform-vars-field platform-vars-flex">';
        
        if ($isBool) {
            $checked = $var['value'] === 'true' ? ' checked' : '';
            $html .= '<span class="platform-vars-toggle">'
                . '<input type="checkbox" data-vars-name="' . htmlspecialchars($var['name'], ENT_QUOTES, 'UTF-8') . '" data-vars-bool="1"' . $checked . '>'
                . '<span class="platform-vars-toggle-track"></span>'
                . '</span>';
        } else {
            $html .= '<input class="platform-input" type="text" data-vars-name="' . htmlspecialchars($var['name'], ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($var['value'], ENT_QUOTES, 'UTF-8') . '">';
        }
        $html .= '<span class="platform-field-label">' . htmlspecialchars($var['name'], ENT_QUOTES, 'UTF-8') . '</span>';
        $html .= '</label>';
    }

    $html .= '</div>';
    $html .= '<div class="platform-vars-actions">'
        . '<button class="platform-button platform-button-sm" type="button" data-vars-save>Save params</button>'
        . '<span class="platform-status" data-vars-status>Idle</span>'
        . '</div>';
    $html .= '</div>';

    return $html;
}
