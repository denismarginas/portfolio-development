<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('ENGINE_ROOT', dirname(__DIR__));
define('ENGINE_DIR', __DIR__);

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/DataService.php';
require_once __DIR__ . '/UrlService.php';
require_once __DIR__ . '/ImageRenderer.php';
require_once __DIR__ . '/Helpers.php';
require_once __DIR__ . '/ComponentRenderer.php';
require_once __DIR__ . '/Functions.php';
require_once __DIR__ . '/ScssCompiler.php';
require_once __DIR__ . '/ScssMinifier.php';
require_once __DIR__ . '/Renderer.php';

EngineConfig::init();
DataService::init();

$GLOBALS['url_path'] = '';

$global_settings = DataService::get_global_settings();
if ($global_settings && !empty($global_settings['url'])) {
    $parsed = parse_url($global_settings['url']);
    $path = $parsed['path'] ?? '';
    $GLOBALS['url_path'] = rtrim($path, '/') . '/';
}

$GLOBALS['urlPath'] = $GLOBALS['url_path'];
