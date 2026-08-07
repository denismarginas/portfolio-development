<?php

PlatformPathService::load_php_dir(__DIR__ . '/parts/');

class image
{
    use image_render, image_sizes;
}
