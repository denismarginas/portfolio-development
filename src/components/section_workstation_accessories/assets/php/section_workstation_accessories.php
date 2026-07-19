<?php

class SectionWorkstationAccessories
{
    public static function render(array $data = []): string
    {
        $workstationData = $data['workstationData'] ?? get_data_json('data_workstation', 'data');

        if (empty($workstationData)) {
            return '';
        }

        $layout = $data['layout'] ?? '';
        $layoutAttr = !empty($layout) ? ' data-layout="' . $layout . '"' : '';

        $path = $workstationData["setups"]["setup 1"]["path-img"] ?? '';
        $products = $workstationData["setups"]["setup 1"]["accessories"] ?? [];

        $sliderContent = render_component('slider_workstation', $data);

        $title = ($workstationData["setups"]["setup 1"]["title"] ?? '') . ' Accessories';

        $productLinks = '';
        foreach ($products as $key => $product) {
            $productLinks .= '<li>' . svg_get('chevron-right') . '<a href="#' . $key . '">' . $product["name"] . '</a></li>';
        }

        $productCards = '';
        foreach ($products as $key => $product) {
            $productCards .= '<div class="product" id="' . $key . '" data-motion="transition-fade-0 transition-slideInRight-0">';
            $productCards .= '<div class="product-image">' . render_image($GLOBALS['urlPath'] . "content/img/" . $path . "/" . $product["img_src"]) . '</div>';
            $productCards .= '<span>' . $product["name"] . '</span>';
            $productCards .= '</div>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ layout_attr }}', '{{ slider_content }}', '{{ title }}', '{{ product_links }}', '{{ product_cards }}'],
            [$layoutAttr, $sliderContent, $title, $productLinks, $productCards],
            $template
        );
    }
}
