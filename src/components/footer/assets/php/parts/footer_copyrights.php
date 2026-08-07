<?php

trait footer_copyrights
{
    protected static function render_copyrights(string $identity): string
    {
        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_copyrights.html', [
            'identity' => htmlspecialchars($identity),
        ]);
    }
}
