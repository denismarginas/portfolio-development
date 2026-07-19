<?php

$root = dirname(__DIR__);
$data_dir = $root . '/src/content/json/data';
$index_dir = $root . '/src/content/json/index';

// Additional hyphenated keys not in the first migration
$extra_keys = [
    'dir-name' => 'dir_name',
    'url-path' => 'url_path',
    'page-text' => 'page_text',
    'button-text' => 'button_text',
    'privacy-policy' => 'privacy_policy',
    'socials-youtube' => 'socials_youtube',
    'socials-facebook' => 'socials_facebook',
    'socials-linkedin' => 'socials_linkedin',
    'socials-instagram' => 'socials_instagram',
    'socials-x' => 'socials_x',
    'socials-github' => 'socials_github',
    'last-update-date' => 'last_update_date',
    'block-1' => 'block_1',
    'block-2' => 'block_2',
    'block-3' => 'block_3',
    'block-4' => 'block_4',
    'icon-svg' => 'icon_svg',
    'background-url' => 'background_url',
    'modal-url' => 'modal_url',
    'aspect-ratio' => 'aspect_ratio',
    'object-fit' => 'object_fit',
    'img-bg' => 'img_bg',
    'display-type' => 'display_type',
    'job-type' => 'job_type',
    'time-period' => 'time_period',
    'button-page-redirect-slug' => 'button_page_redirect_slug',
    'font-family' => 'font_family',
    'img-src-box' => 'img_src_box',
    'media-path' => 'media_path',
    'path-img' => 'path_img',
    'path-vid' => 'path_vid',
    'post-single-type' => 'post_single_type',
    'type-name' => 'type_name',
    'type-parent' => 'type_parent',
    'warning-text' => 'warning_text',
];

// Fix data files
$data_files = glob($data_dir . '/*.json');
$data_updated = 0;

foreach ($data_files as $file_path) {
    $content = file_get_contents($file_path);
    $original = $content;

    foreach ($extra_keys as $hyphen => $underscore) {
        $content = str_replace('"' . $hyphen . '":', '"' . $underscore . '":', $content);
        // Also handle template placeholders
        $content = str_replace('{{' . $hyphen . '}}', '{{' . $underscore . '}}', $content);
        $content = str_replace('{{' . $hyphen . '}', '{{' . $underscore . '}}', $content);
    }

    if ($content !== $original) {
        file_put_contents($file_path, $content);
        $data_updated++;
    }
}

echo "JSON data files updated: $data_updated\n";

// Fix index files
$index_files = glob($index_dir . '/*.json');
$index_updated = 0;

$index_extra_keys = [
    'section-not-found' => 'section_not_found',
];

foreach ($index_files as $file_path) {
    $content = file_get_contents($file_path);
    $original = $content;

    foreach ($index_extra_keys as $hyphen => $underscore) {
        $content = str_replace('"' . $hyphen . '":', '"' . $underscore . '":', $content);
    }

    if ($content !== $original) {
        file_put_contents($file_path, $content);
        $index_updated++;
    }
}

echo "Index files updated: $index_updated\n";
