<?php

class FeatureHero
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;

        $bgTexture = self::render_bg_texture();

        $personal = DataService::get_personal_data();
        $imgData = $personal['post_projects']['img']['background'] ?? [];
        $urlPath = DataService::get_url_path();

        $shape1 = $urlPath . ($imgData['overlay_shape_1'] ?? '');
        $shape2 = $urlPath . ($imgData['overlay_shape_2'] ?? '');

        $shape1Html = '';
        if (!empty($shape1)) {
            $shape1Html = '<svg class="dm-shape-1" aria-hidden="true"><use xlink:href="#' . htmlspecialchars($shape1) . '"></use></svg>';
        }

        $shape2Html = '';
        if (!empty($shape2)) {
            $shape2Html = '<svg class="dm-shape-2" aria-hidden="true"><use xlink:href="#' . htmlspecialchars($shape2) . '"></use></svg>';
        }

        $deviceLayoutDesktop = ComponentRenderer::render_component('device_layout', [
            'post_data' => $postData,
            'type' => 'web',
            'device' => 'desktop',
        ]);

        $deviceLayoutPhone = ComponentRenderer::render_component('device_layout', [
            'post_data' => $postData,
            'type' => 'media',
            'device' => 'phone',
        ]);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ bg_texture }}', '{{ shape_1 }}', '{{ shape_2 }}', '{{ device_layout_desktop }}', '{{ device_layout_phone }}'],
            [$bgTexture, $shape1Html, $shape2Html, $deviceLayoutDesktop, $deviceLayoutPhone],
            $template
        );
    }

    protected static function render_bg_texture(): string
    {
        $personal = DataService::get_personal_data();
        $texture = DataService::get_url_path() . ($personal['post_projects']['img']['background']['overlay_texture'] ?? '');
        if (empty($texture)) {
            return '';
        }
        return '<div class="bg-texture" style="background-image: url(\'' . htmlspecialchars($texture) . '\')"></div>';
    }
}