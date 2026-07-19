<?php

class SectionWorkstationConfiguration
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
        $products = $workstationData["setups"]["setup 1"]["configuration"] ?? [];

        $workstationImage = '';
        $img = $workstationData["setups"]["setup 1"]["images"]["workstation"][1] ?? '';
        if (!empty($path) && !empty($img)) {
            $workstationImage .= render_image($GLOBALS['urlPath'] . "content/img/" . $path . "/" . $img);
        }

        $title = ($workstationData["setups"]["setup 1"]["title"] ?? '') . ' Configuration';

        $productLinks = '';
        foreach ($products as $key => $product) {
            $productLinks .= '<li>' . svg_get('chevron-right') . '<a href="#' . $key . '">' . $product["name"] . '</a></li>';
        }

        $productCards = '';
        foreach ($products as $key => $product) {
            $productCards .= '<div class="product" id="' . $key . '" data-motion="transition-fade-0 transition-slideInRight-0">';
            $productCards .= '<div class="product-image">';
            $productCards .= render_image($GLOBALS['urlPath'] . "content/img/" . $path . "/" . $product["img_src"]);
            if (isset($product["img-src-box"])) {
                $productCards .= render_image($GLOBALS['urlPath'] . "content/img/" . $path . "/" . $product["img-src-box"]);
            }
            $productCards .= '</div>';
            $productCards .= '<span>' . $product["name"] . '</span>';
            $productCards .= '</div>';
}

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ layout_attr }}', '{{ workstation_image }}', '{{ title }}', '{{ product_links }}', '{{ product_cards }}'],
            [$layoutAttr, $workstationImage, $title, $productLinks, $productCards],
            $template
        );
    }
}
