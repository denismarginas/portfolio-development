<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('ENGINE_ROOT', dirname(__DIR__));
define('ENGINE_DIR', __DIR__);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data_service.php';
require_once __DIR__ . '/url_service.php';
require_once __DIR__ . '/image_renderer.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/component_renderer.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/scss_compiler.php';
require_once __DIR__ . '/scss_minifier.php';
require_once __DIR__ . '/renderer.php';

engine_config::init();
data_service::init();

$GLOBALS['url_path'] = '';

$global_settings = data_service::get_global_settings();
if ($global_settings && !empty($global_settings['url'])) {
    $parsed = parse_url($global_settings['url']);
    $path = $parsed['path'] ?? '';
    $GLOBALS['url_path'] = rtrim($path, '/') . '/';
}

$GLOBALS['urlPath'] = $GLOBALS['url_path'];
