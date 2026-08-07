<?php

PlatformPathService::load_php_dir(__DIR__ . '/parts/');

class card
{
    use card_render, card_render_education, card_media, card_meta, card_button, card_resolve;
}