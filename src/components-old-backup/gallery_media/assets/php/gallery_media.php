<?php

class GalleryMedia
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $urlPath = DataService::get_url_path();

        $postType = $postData['post_type'] ?? 'projects';
        $mediaPath = $postData['media_path'] ?? '';

        $srcCurrent = realpath(__DIR__ . '/../../../../content/img/' . $postType . '/' . $mediaPath . '/media/');
        $galleryPath = $urlPath . 'src/content/img/' . $postType . '/' . $mediaPath . '/media/';

        if (!is_dir($srcCurrent)) {
            return '';
        }

        $galleryContent = '';

        $directories = glob($srcCurrent . '*', GLOB_ONLYDIR);
        foreach ($directories as $key => $directory) {
            $dirName = basename($directory);
            $images = Helpers::get_images_in_folder($directory . '/');

            if (!empty($images)) {
                $galleryContent .= '<ul class="dm-media-gallery" data-list-design="' . Helpers::list_design(count($images)) . '" data-slider-container-src="gallery-media-content-' . $key . '">';
                foreach ($images as $img) {
                    $path = $galleryPath . $dirName . '/' . $img;
                    $galleryContent .= '<li class="dm-media-gallery-item" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s">';
                    $galleryContent .= ComponentRenderer::render_component('image', [
                        'src' => $path,
                        'popup' => true,
                        'additionalAttributes' => ['data-slider-item' => 'true', 'data-slider-items-src' => "gallery-media-content-$key"],
                    ]);
                    $galleryContent .= '</li>';
                }
                $galleryContent .= '</ul>';
            }
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace('{{ gallery_content }}', $galleryContent, $template);
    }
}
