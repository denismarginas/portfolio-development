<aside class="platform-sidebar platform-sidebar-right">
    <section class="platform-card">
        <div class="platform-card-header">
            <h2 class="platform-card-title"><?php echo htmlspecialchars($get('sections.inspector'), ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <div class="platform-inspector" data-platform-inspector>
            <p class="platform-empty"><?php echo htmlspecialchars($get('canvas.selectCard'), ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </section>

    <section class="platform-card">
        <div class="platform-card-header">
            <h2 class="platform-card-title"><?php echo htmlspecialchars($get('sections.links'), ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <div class="platform-list" data-platform-link-list></div>
    </section>
</aside>
