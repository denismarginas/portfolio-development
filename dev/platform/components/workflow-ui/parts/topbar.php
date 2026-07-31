<header class="platform-topbar">
    <div class="platform-topbar-copy">
        <span class="platform-kicker"><?php echo htmlspecialchars($get('header.kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
        <h1 class="platform-title"><?php echo htmlspecialchars($get('header.title'), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="platform-description"><?php echo htmlspecialchars($get('header.description'), ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div class="platform-topbar-actions">
        <button class="platform-button platform-button-ghost" type="button" data-platform-action="compile"><?php echo htmlspecialchars($get('actions.compileScss'), ENT_QUOTES, 'UTF-8'); ?></button>
        <button class="platform-button platform-button-ghost" type="button" data-platform-action="reload"><?php echo htmlspecialchars($get('actions.reload'), ENT_QUOTES, 'UTF-8'); ?></button>
        <button class="platform-button platform-button-ghost" type="button" onclick="window.location.href='?page=edit-posts'"><?php echo htmlspecialchars($get('actions.editPosts'), ENT_QUOTES, 'UTF-8'); ?></button>
        <button class="platform-button" type="button" data-platform-action="save"><?php echo htmlspecialchars($get('actions.saveGraph'), ENT_QUOTES, 'UTF-8'); ?></button>
    </div>
</header>
