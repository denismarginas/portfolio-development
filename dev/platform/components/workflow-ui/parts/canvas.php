<section class="platform-canvas-shell">
    <div class="platform-canvas-toolbar">
        <span class="platform-status" data-platform-status><?php echo htmlspecialchars($get('canvas.ready'), ENT_QUOTES, 'UTF-8'); ?></span>
        <span class="platform-hint"><?php echo htmlspecialchars($get('canvas.hint'), ENT_QUOTES, 'UTF-8'); ?></span>
        <div class="platform-canvas-zoom-controls">
            <button class="platform-button platform-button-small" type="button" data-platform-action="fit-view"><?php echo htmlspecialchars($get('canvas.fitView', 'Fit view'), ENT_QUOTES, 'UTF-8'); ?></button>
            <span class="platform-zoom-level" data-platform-zoom>100%</span>
        </div>
    </div>
    <div class="platform-canvas platform-bg-grid" data-platform-canvas>
        <svg class="platform-links" data-platform-links aria-hidden="true"></svg>
        <div class="platform-canvas-viewport" data-platform-viewport>
            <div class="platform-cards" data-platform-cards></div>
        </div>
    </div>
</section>
