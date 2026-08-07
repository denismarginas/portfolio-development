<?php

PlatformPathService::load_php_dir(__DIR__ . '/parts/');

class tab_list
{
    use tab_list_render, tab_list_value;
}