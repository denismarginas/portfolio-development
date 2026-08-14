<?php

class ProjectFeatureHero
{
    public static function render(array $data = []): string
    {
        $postType = $data['post_type'] ?? '';
        $colors = $data['colors'] ?? [];
        $tags = $data['tags'] ?? [];
        $mediaPath = $data['media']['path'] ?? '';

        // Fall back to post_current_data (used by preview script)
        if (empty($mediaPath) && !empty($data['post_current_data'])) {
            $pc = $data['post_current_data'];
            $pcData = $pc['data'] ?? $pc;
            $pcSettings = $pc['settings'] ?? [];

            if (empty($postType)) $postType = $pcSettings['post_type'] ?? 'projects';
            if (empty($colors)) $colors = $pcSettings['appearance']['colors'] ?? [];
            if (empty($tags)) $tags = $pcData['tags'] ?? [];
            if (empty($mediaPath)) $mediaPath = $pcData['media']['path'] ?? '';
        }

        $postType = $postType ?: 'projects';

        $bgTexture = self::render_bg_texture();

        $personal = DataService::get_personal_data();
        $imgData = $personal['post_projects']['img']['background'] ?? [];

        $shape1 = $imgData['overlay_shape_1'] ?? '';
        $shape2 = $imgData['overlay_shape_2'] ?? '';

        $shape1Html = '';
        if (!empty($shape1) && svg_has_icon($shape1)) {
            $shape1Html = str_replace('<svg', '<svg class="dm-shape-1"', svg_get($shape1));
        }

        $shape2Html = '';
        if (!empty($shape2) && svg_has_icon($shape2)) {
            $shape2Html = str_replace('<svg', '<svg class="dm-shape-2"', svg_get($shape2));
        }

        $mappedData = [
            'post_type' => $postType,
            'media_path' => $mediaPath,
            'tags' => $tags,
            'colors' => [
                'post_color_primary' => $colors['primary'] ?? '',
                'post_color_secondary' => $colors['secondary'] ?? '',
            ],
        ];

        $deviceWeb = ComponentRenderer::render_component('devices_post_item_web', [
            'postData' => $mappedData,
        ]);

        $deviceMedia = ComponentRenderer::render_component('devices_post_item_media', [
            'postData' => $mappedData,
        ]);

        $waves = ComponentRenderer::render_component('animation_waves', []);

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ bg_texture }}', '{{ shape_1 }}', '{{ shape_2 }}', '{{ devices_web }}', '{{ devices_media }}', '{{ waves }}'],
            [$bgTexture, $shape1Html, $shape2Html, $deviceWeb, $deviceMedia, $waves],
            $template
        );
    }

    protected static function render_bg_texture(): string
    {
        $personal = DataService::get_personal_data();
        $texturePath = $personal['post_projects']['img']['background']['overlay_texture'] ?? '';
        if (empty($texturePath)) {
            return '';
        }
        $url = DataService::get_url_path() . $texturePath;
        return '<div class="bg-texture" style="background-image: url(\'' . htmlspecialchars($url) . '\')"></div>';
    }
}