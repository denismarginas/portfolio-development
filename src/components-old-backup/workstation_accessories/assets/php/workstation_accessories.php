<?php

class WorkstationAccessories
{
    public static function render(array $data): string
    {
        $post_data = $data['postData'] ?? [];

        if (empty($post_data)) {
            return '';
        }

        $products = $post_data["accessories"] ?? [];
        $imgList = $post_data["images"]["full_setup"] ?? [];

        $imgImgPath = ($post_data["path_img"] ?? "") . "/";
        $imgMediaPath = ($post_data["media_path"] ?? "") . "/";
        $imgPath = $GLOBALS['urlPath'] . "src/content/img/" . $imgImgPath . $imgMediaPath;

        $newImgList = [];
        foreach ($imgList as $imgItem) {
            $newImgList[] = render_image($imgPath . $imgItem, true);
        }
        $imgList = $newImgList;

        $productCards = '';
        foreach ($products as $key => $product) {
            if (isset($product['name'])) {
                $product['img_path'] = $imgPath;
                $productCards .= render_component('workstation_product_card', [
                    'product' => $product,
                    'sliderAttr' => [
                        'data-slider-item' => 'true',
                        'data-slider-items-src' => 'dm-products-config',
                        'data-slider-item-query-attr' => 'dm-product-config'
                    ]
                ]);
            }
        }

        $visualContent = '';
        if ($imgList && count($imgList) > 1) {
            $visualContent = renderSlider($imgList, true, false, true);
        } elseif ($imgList && count($imgList) === 1) {
            $visualContent = '<div class="card-bg">' . $imgList[0] . '</div>';
        } else {
            $visualContent = '<div class="card-bg">' . svg_get("workstation-desk") . '</div>';
        }

        $enumeration = '';
        foreach ($products as $key => $product) {
            if (isset($product['name'])) {
                $enumeration .= '<li>';
                $enumeration .= svg_get("chevron-right");
                $enumeration .= '<span class="text">' . htmlspecialchars($product['name']) . '</span>';
                $enumeration .= '</li>';
            }
        }

        return self::render_template([
            'product_cards' => $productCards,
            'visual_content' => $visualContent,
            'enumeration' => $enumeration,
        ]);
    }

    protected static function render_template(array $data): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }
        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }
        return $html;
    }
}
