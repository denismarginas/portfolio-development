<?php

PlatformPathService::load_php_dir(__DIR__ . '/parts/');

class footer
{
    use footer_render, footer_copyrights;
}
