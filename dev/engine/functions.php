<?php

/**
 * Global function wrappers used by component PHP files.
 * snake_case = canonical names
 * camelCase = aliases for backward compatibility (will be removed after migration)
 */

// ─── Canonical snake_case ───

function get_data_json(string $name, string $sub_dir = 'data'): ?array
{
    return data_service::get_data($name, $sub_dir);
}

function render_component(string $component_name, array $data = []): string
{
    return component_renderer::render($component_name, $data);
}

function get_component_asset_tags(): string
{
    return component_renderer::get_component_asset_tags();
}

function seo_implicit_fields(): array
{
    $fields = [];
    $global = data_service::get_global_settings();

    $site_name = $global['site_identity'] ?? '';
    $description = $global['site_description'] ?? '';
    $favicon = $global['favicon'] ?? '';
    $url_path = engine_config::getUrlPath();

    if (!empty($favicon)) {
        $favicon_url = (str_starts_with($favicon, 'http') || str_starts_with($favicon, '/')) ? $favicon : $url_path . ltrim($favicon, '/');
        $fields[] = '<link rel="icon" type="image/x-icon" href="' . htmlspecialchars($favicon_url) . '">';
    }
    if (!empty($site_name)) {
        $fields[] = '<meta name="application-name" content="' . htmlspecialchars($site_name) . '">';
    }
    if (!empty($description)) {
        $fields[] = '<meta name="description" content="' . htmlspecialchars($description) . '">';
    }
    $fields[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $fields[] = '<meta charset="UTF-8">';

    $gtag = $global['google_analytics']['id'] ?? '';
    if (!empty($gtag)) {
        $fields[] = '<meta name="google-analytics" content="' . htmlspecialchars($gtag) . '">';
    }
    $verification = $global['google_site_verification'] ?? '';
    if (!empty($verification)) {
        $fields[] = '<meta name="google-site-verification" content="' . htmlspecialchars($verification) . '">';
    }

    return $fields;
}

function seo_add_in_content(array $seo_data, string $existing_html = ''): string
{
    $html = $existing_html;

    if (!empty($seo_data['title'])) {
        $html .= '<title>' . htmlspecialchars($seo_data['title']) . '</title>';
    }

    foreach (['description' => 'description', 'keywords' => 'keywords'] as $key => $meta) {
        if (!empty($seo_data[$key])) {
            $html .= '<meta name="' . htmlspecialchars($meta) . '" content="' . htmlspecialchars($seo_data[$key]) . '">';
        }
    }

    $slug = $seo_data['slug'] ?? '';
    if (!empty($slug)) {
        $ext = engine_config::getPageSlugExtension();
        $html .= '<link rel="canonical" href="' . htmlspecialchars($slug . $ext) . '">';
        $index = $seo_data['index'] ?? 'true';
        $html .= ($index === 'true' || $index === true)
            ? '<meta name="robots" content="index, follow">'
            : '<meta name="robots" content="noindex, nofollow">';
    }

    return $html;
}

function get_seo_from_current_page_data(string $filename): ?array
{
    $filename = basename($filename, '.html');
    foreach (data_service::get_valid_post_files() as $pf) {
        $posts = data_service::get_all_posts_from_file($pf['name']);
        if (empty($posts)) continue;
        foreach ($posts as $post) {
            $seo = $post['seo'] ?? [];
            if (($seo['slug'] ?? '') === $filename) return $seo;
        }
    }
    return null;
}

function get_seo_from_current_post_project_data(array $post_data): ?array
{
    return $post_data['seo'] ?? null;
}

function render_image(string $src, bool $lazy = false, string $class = '', bool $has_overlay = false, array $attributes = []): string
{
    return helpers::render_image($src, $lazy, $class, $has_overlay, $attributes);
}

function render_bg_img_overlay_texture(): string
{
    return helpers::render_bg_img_overlay_texture();
}

function execute_php_in_string(string $str): string
{
    return helpers::execute_php_in_string($str);
}

function check_echo(mixed $value): void
{
    helpers::check_echo($value);
}

function change_space_with_hyphen_and_lowercase(string $str): string
{
    return helpers::change_space_with_hyphen_and_lowercase($str);
}

function remove_space_and_lowercase(string $str): string
{
    return helpers::remove_space_and_lowercase($str);
}

function add_https(string $url): string
{
    return helpers::add_https($url);
}

function remove_https(string $url): string
{
    return helpers::remove_https($url);
}

function svg_get(string $icon): string
{
    static $icons_data = null;
    if ($icons_data === null) {
        $data_file = engine_config::get('data_dir') . '/data_icons.json';
        if (file_exists($data_file)) {
            $icons_data = json_decode(file_get_contents($data_file), true);
        } else {
            $icons_data = [];
        }
    }

    $normalized = str_replace('-', '_', $icon);
    $icon_data = $icons_data[$normalized] ?? $icons_data[$icon] ?? null;
    if ($icon_data === null) return '<!-- SVG not found: ' . htmlspecialchars($icon) . ' -->';

    if (is_string($icon_data)) {
        return $icon_data;
    }

    $svg = $icon_data['svg'] ?? $icon_data['markup'] ?? '';
    if (!empty($icon_data['viewBox'])) {
        $svg = str_replace('<svg', '<svg viewBox="' . htmlspecialchars($icon_data['viewBox']) . '"', $svg);
    }

    return $svg;
}

function svg_has_icon(string $icon): bool
{
    static $icons_data = null;
    if ($icons_data === null) {
        $data_file = engine_config::get('data_dir') . '/data_icons.json';
        if (file_exists($data_file)) {
            $icons_data = json_decode(file_get_contents($data_file), true);
        } else {
            $icons_data = [];
        }
    }

    $normalized = str_replace('-', '_', $icon);
    return isset($icons_data[$normalized]) || isset($icons_data[$icon]);
}

function svg_render(string $icon): void
{
    echo svg_get($icon);
}

function extract_year_from_date_string(string $date_str): string
{
    if (empty($date_str)) return '';
    $parts = explode('-', $date_str);
    return end($parts);
}

// ─── camelCase aliases (backward compat, removed after migration) ───

if (!function_exists('getDataJson')) {
    function getDataJson(string $name, string $subDir = 'data'): ?array { return get_data_json($name, $subDir); }
}
if (!function_exists('renderComponent')) {
    function renderComponent(string $componentName, array $data = []): string { return render_component($componentName, $data); }
}
if (!function_exists('getComponentAssetTags')) {
    function getComponentAssetTags(): string { return get_component_asset_tags(); }
}
if (!function_exists('seoImplicitFields')) {
    function seoImplicitFields(): array { return seo_implicit_fields(); }
}
if (!function_exists('seoAddInContent')) {
    function seoAddInContent(array $seoData, string $existingHtml = ''): string { return seo_add_in_content($seoData, $existingHtml); }
}
if (!function_exists('getSeoFromCurrentPageData')) {
    function getSeoFromCurrentPageData(string $filename): ?array { return get_seo_from_current_page_data($filename); }
}
if (!function_exists('getSeoFromCurrentPostProjectData')) {
    function getSeoFromCurrentPostProjectData(array $postData): ?array { return get_seo_from_current_post_project_data($postData); }
}
if (!function_exists('renderImage')) {
    function renderImage(string $src, bool $lazy = false, string $class = '', bool $hasOverlay = false, array $attributes = []): string { return render_image($src, $lazy, $class, $hasOverlay, $attributes); }
}
if (!function_exists('renderBgImgOverlayTexture')) {
    function renderBgImgOverlayTexture(): string { return render_bg_img_overlay_texture(); }
}
if (!function_exists('executePhpInString')) {
    function executePhpInString(string $str): string { return execute_php_in_string($str); }
}
if (!function_exists('checkEcho')) {
    function checkEcho(mixed $value): void { check_echo($value); }
}
if (!function_exists('changeSpaceWithHyphenAndLowercase')) {
    function changeSpaceWithHyphenAndLowercase(string $str): string { return change_space_with_hyphen_and_lowercase($str); }
}
if (!function_exists('removeSpaceAndLowercase')) {
    function removeSpaceAndLowercase(string $str): string { return remove_space_and_lowercase($str); }
}
if (!function_exists('addHttps')) {
    function addHttps(string $url): string { return add_https($url); }
}
if (!function_exists('removeHttps')) {
    function removeHttps(string $url): string { return remove_https($url); }
}
if (!function_exists('svgGet')) {
    function svgGet(string $icon): string { return svg_get($icon); }
}
if (!function_exists('svgHasIcon')) {
    function svgHasIcon(string $icon): bool { return svg_has_icon($icon); }
}
if (!function_exists('svgRender')) {
    function svgRender(string $icon): void { svg_render($icon); }
}
if (!function_exists('extractYearFromDateString')) {
    function extractYearFromDateString(string $dateStr): string { return extract_year_from_date_string($dateStr); }
}
