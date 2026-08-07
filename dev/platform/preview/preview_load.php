<?php

require_once __DIR__ . '/engine-bridge.php';
require_once __DIR__ . '/../core/autoload.php';

function platform_load_preview_state(): array
{
    $cardsPath = ENGINE_PROJECT_ROOT . '/dev/platform/data/cards.json';
    $debugMode = false;
    $compileAssets = false;
    $htmlCompile = false;
    $htmlCompileFolder = false;
    $structureVars = [];
    $seoVars = [];
    $globalPaths = [];
    $compileScssFlags = [
        'compile_scss_platform_components' => true,
        'compile_scss_platform_assets' => true,
        'compile_scss_src_components' => true,
        'compile_scss_assets' => false,
        'compile_scss_everytime' => true,
    ];

    if (file_exists($cardsPath)) {
        $graph = json_decode(file_get_contents($cardsPath), true);
        if ($graph && !empty($graph['cards'])) {
            foreach ($graph['cards'] as $card) {
                $type = $card['type'] ?? '';
                $resolvedVars = PlatformData::resolveCardVariables($card['variables'] ?? []);
                foreach ($resolvedVars as $var) {
                    if ($type === 'project_structure' || $type === 'page_structure') {
                        $structureVars[$type][$var['name']] = $var['value'];
                    } elseif ($type === 'seo_project' || $type === 'seo_page') {
                        $seoVars[$type][$var['name']] = $var['value'];
                    } elseif ($type === 'render') {
                        if ($var['name'] === 'debug_post_data' && $var['value'] === 'true') {
                            $debugMode = true;
                        }
                        if ($var['name'] === 'compile_assets') {
                            $compileAssets = $var['value'] === 'true';
                        }
                        if ($var['name'] === 'html_compile') {
                            $htmlCompile = $var['value'] === 'true';
                        }
                        if ($var['name'] === 'html_compile_folder') {
                            $htmlCompileFolder = $var['value'] === 'true';
                        }
                    } elseif ($type === 'compile_scss') {
                        if (array_key_exists($var['name'], $compileScssFlags)) {
                            $compileScssFlags[$var['name']] = $var['value'] === 'true';
                        }
                    } elseif ($type === 'selectfile') {
                        $postTypeVar = '';
                        $globalVars = [];
                        foreach ($resolvedVars as $cv) {
                            if ($cv['name'] === 'post_type') { $postTypeVar = $cv['value']; }
                            elseif (str_starts_with($cv['name'], 'global_')) { $globalVars[$cv['name']] = $cv['value']; }
                        }
                        if ($postTypeVar) {
                            $globalPaths[$postTypeVar] = $globalVars;
                        }
                    }
                }
            }
        }
    }

    if (isset($_GET['debug_post_data']) && $_GET['debug_post_data'] === 'true') {
        $debugMode = true;
    }

    if ($compileAssets) {
        $compileScssFlags['compile_scss_assets'] = true;
    }

    return [
        'debug_mode' => $debugMode,
        'compile_assets' => $compileAssets,
        'html_compile' => $htmlCompile,
        'html_compile_folder' => $htmlCompileFolder,
        'structure_vars' => $structureVars,
        'seo_vars' => $seoVars,
        'global_paths' => $globalPaths,
        'compile_scss_flags' => $compileScssFlags,
    ];
}
