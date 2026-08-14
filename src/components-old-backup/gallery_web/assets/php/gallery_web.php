<?php

class GalleryWeb
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $galleryType = $data['gallery_type'] ?? 'web';
        $urlPath = DataService::get_url_path();

        $postType = $postData['post_type'] ?? 'projects';
        $mediaPath = $postData['media_path'] ?? '';

        $galleryPath = $urlPath . 'src/content/img/' . $postType . '/' . $mediaPath . '/web/';
        $srcCurrent = realpath(__DIR__ . '/../../../../content/img/' . $postType . '/' . $mediaPath . '/web/');

        $html = '';

        if ($galleryType === 'web') {
            $html = self::render_web_gallery($postData, $galleryPath, $srcCurrent);
        } elseif ($galleryType === 'media') {
            $html = self::render_web_media_gallery($postData, $galleryPath, $srcCurrent);
        } elseif ($galleryType === 'content') {
            $html = self::render_web_content_gallery($postData, $galleryPath, $srcCurrent);
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace('{{ gallery_content }}', $html, $template);
    }

    protected static function render_web_gallery(array $postData, string $galleryPath, string $srcCurrent): string
    {
        $urlPath = DataService::get_url_path();
        $html = '<div id="web" class="dm-gallery-web-content" data-motion="transition-fade-0" data-duration="0.5s">';

        $homeImages = Helpers::get_images_in_folder($srcCurrent . 'home/');
        if (!empty($homeImages)) {
            $html .= '<div class="dm-web-home-banner" data-motion="transition-fade-0" data-duration="0.8s">';
            foreach ($homeImages as $img) {
                $html .= ComponentRenderer::render_component('image', ['src' => $galleryPath . 'home/' . $img, 'popup' => true]);
            }
            $html .= '</div>';
        }

        $desktopImages = Helpers::get_images_in_folder($srcCurrent . 'desktop/');
        if (!empty($desktopImages)) {
            $html .= '<ul class="dm-web-gallery" data-slider-container-src="dm-web-post-gallery">';
            foreach ($desktopImages as $img) {
                $html .= '<li class="dm-web-gallery-item gallery-item-web" data-motion="transition-fade-0" data-duration="0.3s">';
                $html .= '<div class="bg-texture"></div>';
                $html .= ComponentRenderer::render_component('device_layout', [
                    'post_data' => $postData,
                    'device' => 'desktop',
                ]);
                $html .= ComponentRenderer::render_component('image', [
                    'src' => $galleryPath . 'desktop/' . $img,
                    'popup' => true,
                    'additionalAttributes' => ['data-slider-item' => 'true', 'data-slider-items-src' => 'dm-web-post-gallery'],
                ]);
                $html .= '</li>';
            }

            $phoneImages = Helpers::get_images_in_folder($srcCurrent . 'phone/');
            foreach ($phoneImages as $img) {
                $html .= '<li class="dm-web-gallery-item gallery-item-phone" data-motion="transition-fade-0" data-duration="0.3s">';
                $html .= '<div class="bg-texture"></div>';
                $html .= ComponentRenderer::render_component('device_layout', [
                    'post_data' => $postData,
                    'device' => 'phone',
                ]);
                $html .= ComponentRenderer::render_component('image', [
                    'src' => $galleryPath . 'phone/' . $img,
                    'popup' => true,
                    'additionalAttributes' => ['data-slider-item' => 'true', 'data-slider-items-src' => 'dm-web-post-gallery'],
                ]);
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        $html .= '</div>';
        return $html;
    }

    protected static function render_web_media_gallery(array $postData, string $galleryPath, string $srcCurrent): string
    {
        $urlPath = DataService::get_url_path();
        $html = '<div id="media-web" class="dm-gallery-web-content" data-motion="transition-fade-0" data-duration="0.5s">';

        $mediaPath = $srcCurrent . 'media-website/';
        $mediaGalleryPath = $galleryPath . 'media-website/';

        if (!is_dir($mediaPath)) {
            return $html . '</div>';
        }

        $images = Helpers::get_images_in_folder($mediaPath);
        $dirs = Helpers::get_directories_in_folder($mediaPath);

        if (!empty($images)) {
            $html .= '<ul class="dm-web-media-gallery" data-list-design="' . Helpers::list_design(count($images)) . '" data-slider-container-src="dm-gallery-web-content" data-motion="transition-fade-0" data-duration="0.8s">';
            foreach ($images as $item) {
                $html .= '<li class="dm-web-media-gallery-item" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s">';
                $html .= ComponentRenderer::render_component('image', [
                    'src' => $mediaGalleryPath . $item,
                    'popup' => true,
                    'additionalAttributes' => ['data-slider-item' => 'true', 'data-slider-items-src' => 'dm-gallery-web-content'],
                ]);
                $html .= '<div style="background-image: url(\'' . $mediaGalleryPath . $item . '\')"></div></li>';
            }
            $html .= '</ul>';
        }

        foreach ($dirs as $key => $dir) {
            $dirImages = Helpers::get_images_in_folder($mediaPath . $dir . '/');
            if (!empty($dirImages)) {
                $html .= '<ul class="dm-web-media-gallery" data-list-design="' . Helpers::list_design(count($dirImages)) . '" data-slider-container-src="dm-gallery-web-content-' . $key . '"';
                if ($dir === 'logo' || $dir === 'favicon') {
                    $html .= ' data-list-item="logo"';
                }
                $html .= ' data-motion="transition-fade-0" data-duration="0.8s">';
                foreach ($dirImages as $item) {
                    $path = $mediaGalleryPath . $dir . '/' . $item;
                    $html .= '<li class="dm-web-media-gallery-item" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s">';
                    $html .= ComponentRenderer::render_component('image', [
                        'src' => $path,
                        'popup' => true,
                        'additionalAttributes' => ['data-slider-item' => 'true', 'data-slider-items-src' => "dm-gallery-web-content-$key"],
                    ]);
                    $html .= '<div style="background-image: url(\'' . $path . '\')"></div></li>';
                }
                $html .= '</ul>';
            }
        }

        $html .= '</div>';
        return $html;
    }

    protected static function render_web_content_gallery(array $postData, string $galleryPath, string $srcCurrent): string
    {
        $urlPath = DataService::get_url_path();
        $html = '<div id="web-content" class="dm-gallery-web-content" data-motion="transition-fade-0" data-duration="0.5s">';

        $contentPath = $srcCurrent . 'content-website/';
        $contentGalleryPath = $galleryPath . 'content-website/';

        if (!is_dir($contentPath)) {
            return $html . '</div>';
        }

        $dirs = Helpers::get_directories_in_folder($contentPath);
        foreach ($dirs as $key => $dir) {
            $desktopPath = $contentPath . $dir . '/desktop/';
            $phonePath = $contentPath . $dir . '/phone/';
            $desktopImages = is_dir($desktopPath) ? Helpers::get_images_in_folder($desktopPath) : [];
            $phoneImages = is_dir($phonePath) ? Helpers::get_images_in_folder($phonePath) : [];
            $nrItems = count($desktopImages) + count($phoneImages);

            $html .= '<ul id="content-web" class="dm-web-gallery" data-list-design="' . Helpers::list_design($nrItems) . '" data-slider-container-src="dm-gallery-web-content-' . $key . '">';

            foreach ($desktopImages as $img) {
                $html .= '<li class="dm-web-gallery-item gallery-item-web" data-motion="transition-fade-0" data-duration="0.3s">';
                $html .= '<div class="bg-texture"></div>';
                $html .= ComponentRenderer::render_component('device_layout', ['post_data' => $postData, 'device' => 'desktop']);
                $html .= ComponentRenderer::render_component('image', [
                    'src' => $contentGalleryPath . $dir . '/desktop/' . $img,
                    'popup' => true,
                    'additionalAttributes' => ['data-slider-item' => 'true', 'data-slider-items-src' => "dm-gallery-web-content-$key"],
                ]);
                $html .= '</li>';
            }

            foreach ($phoneImages as $img) {
                $html .= '<li class="dm-web-gallery-item gallery-item-phone" data-motion="transition-fade-0" data-duration="0.3s">';
                $html .= '<div class="bg-texture"></div>';
                $html .= ComponentRenderer::render_component('device_layout', ['post_data' => $postData, 'device' => 'phone']);
                $html .= ComponentRenderer::render_component('image', [
                    'src' => $contentGalleryPath . $dir . '/phone/' . $img,
                    'popup' => true,
                    'additionalAttributes' => ['data-slider-item' => 'true', 'data-slider-items-src' => "dm-gallery-web-content-$key"],
                ]);
                $html .= '</li>';
            }

            $html .= '</ul>';
        }

        $html .= '</div>';
        return $html;
    }
}
