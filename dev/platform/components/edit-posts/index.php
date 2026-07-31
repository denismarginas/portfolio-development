<?php

require __DIR__ . '/../../core/autoload.php';

$pageTitle = platform_data::getString('editPosts.pageTitle', 'Post Editor') . ' ' . platform_data::getString('pageTitleSuffix', '| Platform');
$metaDesc = platform_data::getString('editPosts.metaDescription', 'Edit portfolio posts with a visual content builder.');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="components/edit-posts/assets/css/edit-posts.css">
    <link rel="stylesheet" href="components/json-editor/assets/css/json-editor.css">
    <link rel="stylesheet" href="components/image/assets/css/image.css">
</head>
<body class="platform-body">
    <div class="platform-shell" data-ep-root>
        <header class="platform-topbar">
            <div class="platform-topbar-copy">
                <span class="platform-kicker" data-ep-i18n="editPosts.kicker">Edit Posts</span>
                <h1 class="platform-title" data-ep-i18n="editPosts.pageTitle">Post Editor</h1>
                <p class="platform-description" data-ep-i18n="editPosts.metaDescription">Select a post type and post to edit its fields and content.</p>
            </div>
            <div class="platform-topbar-actions">
                <button class="platform-button platform-button-ghost" type="button" data-ep-action="back" data-ep-i18n="editPosts.back">&#8592; Back to Workflow</button>
            </div>
        </header>

        <div class="ep-layout">
            <aside class="ep-sidebar">
                <div class="platform-card">
                    <div class="platform-card-header">
                        <h2 class="platform-card-title" data-ep-i18n="editPosts.postTypes">Post Types</h2>
                    </div>
                    <div class="platform-list" data-ep-post-types><div class="platform-list-item" style="color:var(--platform-color-text-secondary)" data-ep-i18n="editPosts.loadingPostTypes">Loading post types...</div></div>
                </div>
                <div class="platform-card" data-ep-post-list-wrap style="display:none;max-height: 600px;overflow: auto;">
                    <div class="platform-card-header">
                        <h2 class="platform-card-title" data-ep-i18n="editPosts.posts">Posts</h2>
                    </div>
                    <div class="platform-form-row">
                        <input class="platform-input" type="text" data-ep-search data-ep-placeholder="editPosts.searchPosts" placeholder="Search posts..." style="width:100%">
                    </div>
                    <div class="platform-list" data-ep-post-list></div>
                </div>
            </aside>

            <main class="ep-main" data-ep-editor style="display:none">
                <div class="platform-status" data-ep-status></div>

                <section class="platform-card">
                    <div class="platform-card-header">
                        <h2 class="platform-card-title" data-ep-post-title data-ep-i18n="editPosts.post">Post</h2>
                        <div class="platform-topbar-actions">
                            <span class="platform-tag" data-ep-post-id></span>
                            <a class="platform-button platform-button-sm platform-button-ghost" data-ep-action="view" href="#" target="_blank" style="display:none">&#8599; View</a>
                            <button class="platform-button platform-button-sm platform-button-ghost" type="button" data-ep-action="duplicate" data-ep-i18n="editPosts.duplicate">&#128203; Duplicate</button>
                            <button class="platform-button platform-button-sm platform-button-ghost" type="button" data-ep-action="delete" data-ep-i18n="editPosts.delete" style="color:var(--platform-danger)">&#128465; Delete</button>
                            <button class="platform-button platform-button-sm" type="button" data-ep-action="save" data-ep-i18n="editPosts.save">Save Post</button>
                            <label class="platform-je-toggle-label">
                                <input class="platform-checkbox" type="checkbox" data-ep-toggle-json> <span data-ep-i18n="editPosts.jsonMode">JSON</span>
                            </label>
                        </div>
                    </div>
                    <div class="platform-card-body platform-bg-grid platform-cards-container">
                        <div class="platform-cards-container" data-ep-fields-container></div>
                        <div data-ep-json-editor style="display:none"></div>
                    </div>
                </section>

                <section class="platform-card">
                    <div class="platform-card-header">
                        <h2 class="platform-card-title" data-ep-i18n="editPosts.contentBuilder">Content Builder</h2>
                        <div class="platform-topbar-actions">
                            <button class="platform-button platform-button-sm" type="button" data-ep-action="toggle-components" data-ep-i18n="editPosts.addComponent">&#43; Add Component</button>
                        </div>
                    </div>
                    <div class="platform-card-body platform-bg-grid">
                        <div class="ep-content-list" data-ep-content-list>
                            <div class="ep-content-empty" data-ep-i18n="editPosts.noContent">No content items. Click "Add Component" to add one.</div>
                        </div>
                    </div>
                </section>

                <div class="ep-component-palette" data-ep-palette style="display:none">
                    <div class="platform-card">
                        <div class="platform-card-header">
                            <h2 class="platform-card-title" data-ep-i18n="editPosts.components">Components</h2>
                            <button class="platform-button platform-button-sm platform-button-ghost" type="button" data-ep-action="close-palette">&times;</button>
                        </div>
                        <div class="platform-card-body">
                            <div class="platform-form-row">
                                <input class="platform-input" type="text" data-ep-palette-search data-ep-placeholder="editPosts.filterComponents" placeholder="Filter components..." style="width:100%">
                            </div>
                            <div class="ep-palette-grid" data-ep-palette-list></div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <div class="platform-status" data-platform-status data-ep-i18n="editPosts.ready">Ready</div>
    </div>

    <script src="assets/js/platform.js" defer></script>
    <script src="components/json-editor/assets/js/json-editor.js" defer></script>
    <script src="components/edit-posts/assets/js/edit-posts-state.js" defer></script>
    <script src="components/edit-posts/assets/js/edit-posts-utils.js" defer></script>
    <script src="components/edit-posts/assets/js/edit-posts-core.js" defer></script>
    <script src="components/edit-posts/assets/js/edit-posts-editor.js" defer></script>
    <script src="components/edit-posts/assets/js/edit-posts-content.js" defer></script>
    <script src="components/edit-posts/assets/js/edit-posts-init.js" defer></script>
    <script src="components/edit-posts/assets/js/img_preview_from_json.js" defer></script>
</body>
</html>
