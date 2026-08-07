<?php

PlatformPathService::load_php_dir(__DIR__ . '/parts/');

class header
{
    use header_render, header_logo, header_menu, header_search_button, header_theme_toggle, header_heading;
}
