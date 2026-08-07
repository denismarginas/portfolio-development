<?php

function platform_render_generate_fragment(string $tab = ''): string
{
    platform_asset('css', 'components/generate/assets/css/generate.css');
    platform_asset('js', 'components/generate/assets/js/generate.js');

    $tab = in_array($tab, ['translate'], true) ? $tab : 'translate';

    $cards = [];
    foreach (platform_menu()['generate']['children'] as $childKey => $child) {
        $cards[] = [
            'title'  => $child['label'],
            'desc'   => $child['desc'] ?? '',
            'icon'   => $child['icon'],
            'url'    => $child['url'],
            'active' => $tab === $childKey,
        ];
    }

    ob_start();
    ?>
    <div class="platform-admin-page" data-generate-root data-generate-tab="<?php echo htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="platform-page-heading">
            <h2 class="platform-title">Generate</h2>
            <p class="platform-description">Generate site artifacts from content data.</p>
        </div>

        <?php echo platform_render_cards($cards); ?>

        <section class="platform-generate-panel" data-generate-panel-translate>
            <div class="platform-card">
                <div class="platform-card-header">
                    <h2 class="platform-card-title">Translate</h2>
                </div>
                <div class="platform-generate-toolbar">
                    <button class="platform-button" type="button" data-generate-action="translate"><?php echo PlatformSvg::render(['name' => 'update', 'size' => 16]); ?>Run translation</button>
                    <span class="platform-status" data-generate-status>Idle</span>
                </div>
                <div class="platform-generate-results" data-generate-results></div>
                <?php echo platform_render_vars('translation', 'translate'); ?>
            </div>
        </section>
    </div>
    <?php
    return ob_get_clean();
}
