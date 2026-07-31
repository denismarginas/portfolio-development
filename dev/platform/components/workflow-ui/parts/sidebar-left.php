<aside class="platform-sidebar platform-sidebar-left">
    <section class="platform-card platform-card-form">
        <div class="platform-card-header">
            <h2 class="platform-card-title"><?php echo htmlspecialchars($get('sections.createCard'), ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <div class="platform-templates" data-platform-templates>
            <button class="platform-button platform-button-small" type="button" data-template="database">DataBase</button>
            <button class="platform-button platform-button-small" type="button" data-template="selectfile">Select File</button>
            <button class="platform-button platform-button-small" type="button" data-template="project_structure">Project Structure</button>
            <button class="platform-button platform-button-small" type="button" data-template="page_structure">Page Structure</button>
            <button class="platform-button platform-button-small" type="button" data-template="seo_project">Seo Projects</button>
            <button class="platform-button platform-button-small" type="button" data-template="seo_page">Seo Page</button>
            <button class="platform-button platform-button-small" type="button" data-template="render">Preview Render</button>
            <button class="platform-button platform-button-small platform-button-ghost" type="button" data-template="custom">Custom</button>
        </div>
        <form class="platform-form" data-platform-card-form>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.title'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="title" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.title'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.type'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="type" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.type'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.action'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="action" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.action'), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.inputs'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="inputs" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.inputs'), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.outputs'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="outputs" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.outputs'), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.variables'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="variables" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.variables'), ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <button class="platform-button platform-button-wide" type="submit"><?php echo htmlspecialchars($get('actions.addCard'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
    </section>

    <section class="platform-card platform-card-form">
        <div class="platform-card-header">
            <h2 class="platform-card-title"><?php echo htmlspecialchars($get('sections.setVariable'), ENT_QUOTES, 'UTF-8'); ?></h2>
        </div>
        <form class="platform-form platform-form-inline" data-platform-variable-form>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.name'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="name" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.name'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
            <label class="platform-field">
                <span class="platform-field-label"><?php echo htmlspecialchars($get('fields.value'), ENT_QUOTES, 'UTF-8'); ?></span>
                <input class="platform-input" name="value" type="text" placeholder="<?php echo htmlspecialchars($get('placeholders.value'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </label>
            <button class="platform-button platform-button-wide" type="submit"><?php echo htmlspecialchars($get('actions.saveVariable'), ENT_QUOTES, 'UTF-8'); ?></button>
        </form>
        <div class="platform-list" data-platform-variable-list></div>
    </section>
</aside>
