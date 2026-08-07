<?php

PlatformPathService::load_php_dir(__DIR__ . '/parts/');

class post_items
{
    use post_items_render, post_items_item, post_items_image, post_items_value;
}
