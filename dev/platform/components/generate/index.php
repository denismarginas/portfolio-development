<?php

function platform_render_generate_fragment(string $tab = ''): string
{
    platform_asset('css', 'components/generate/assets/css/generate.css');
    platform_asset('js', 'components/generate/assets/js/generate.js');

    $tabs = array_keys(platform_menu()['generate']['children']);
    $tab = in_array($tab, $tabs, true) ? $tab : 'translate';

    $icon = static function (string $name) {
        return PlatformSvg::render(['name' => $name, 'size' => 16]);
    };

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

    $visible = static function (string $panel) use ($tab): string {
        return $tab === $panel ? '' : ' style="display:none"';
    };

    ob_start();
    ?>
    <div class="platform-admin-page" data-generate-root data-generate-tab="<?php echo htmlspecialchars($tab, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="platform-page-heading">
            <h2 class="platform-title">Generate</h2>
            <p class="platform-description">Generate site artifacts from content data.</p>
        </div>

        <?php echo platform_render_cards($cards); ?>

        <section class="platform-generate-panel" data-generate-panel-translate<?php echo $visible('translate'); ?>>
            <div class="platform-card">
                <div class="platform-card-header">
                    <h2 class="platform-card-title">Translate</h2>
                </div>
                <div class="platform-generate-toolbar">
                    <button class="platform-button" type="button" data-generate-action="translate"><?php echo $icon('update'); ?>Run translation</button>
                    <span class="platform-status" data-generate-status>Idle</span>
                </div>
                <div class="platform-generate-results" data-generate-results></div>
                <?php echo platform_render_vars('translation', 'translate'); ?>
            </div>
        </section>

        <section class="platform-generate-panel" data-generate-panel-thumbnails<?php echo $visible('thumbnails'); ?>>
            <div class="platform-card">
                <div class="platform-card-header">
                    <h2 class="platform-card-title">Thumbnails</h2>
                </div>
                <p class="platform-description">Builds <code>thumbnail/thumbnail_&lt;project&gt;[_web|_media].webp</code> for render:true projects from the template in <code>dev/platform/assets/img/project_thumbnail</code>.</p>
                <div class="platform-generate-warnings" data-thumbs-warnings></div>
                <div class="platform-generate-toolbar">
                    <select class="platform-input platform-generate-select" data-thumbs-project>
                        <option value="">Loading projects...</option>
                    </select>
                    <select class="platform-input platform-generate-select-sm" data-thumbs-variant>
                        <option value="">Variants from params</option>
                        <option value="default">default</option>
                        <option value="web">web</option>
                        <option value="media">media</option>
                    </select>
                    <button class="platform-button" type="button" data-thumbs-action="preview"><?php echo $icon('view'); ?>Preview</button>
                    <button class="platform-button" type="button" data-thumbs-action="generate"><?php echo $icon('generate'); ?>Generate</button>
                    <button class="platform-button" type="button" data-thumbs-action="stop" style="display:none"><?php echo $icon('close'); ?>Stop</button>
                    <span class="platform-status" data-thumbs-status>Idle</span>
                </div>
                <div class="platform-generate-thumbs" data-thumbs-results></div>
                <?php echo platform_render_vars('thumbnails', 'generate-thumbnails'); ?>
            </div>
        </section>
    </div>
    <?php
    return ob_get_clean();
}
