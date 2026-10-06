<?php

/**
 * The logo is its own component now (src/components/logo); the header just renders
 * its "header" variant. render_logo() / resolve_logo_img() are kept for anything
 * that still calls them.
 */
class header_logo extends header_menu
{
    public static function render_logo(array $settings = []): string
    {
        return PlatformComponentRenderer::render('logo', [
            'variant' => 'header',
            'shape' => 'circle',
        ]);
    }

    public static function resolve_logo_img(string $logoImg): string
    {
        PlatformComponentRenderer::load_component_class('logo');
        return class_exists('logo', false) ? logo::resolve_logo_img($logoImg) : '';
    }
}
