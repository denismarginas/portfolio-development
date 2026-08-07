<?php

header('Content-Type: application/json; charset=UTF-8');

if (!defined('ENGINE_PROJECT_ROOT')) {
    define('ENGINE_PROJECT_ROOT', dirname(__DIR__, 3));
}

require_once ENGINE_PROJECT_ROOT . '/dev/engine/bootstrap.php';

$config = PlatformTranslationBuilder::language_config();
$result = [
    'default' => $config['default'],
    'languages' => $config['list'],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $result = array_merge($result, PlatformTranslationBuilder::run());
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
