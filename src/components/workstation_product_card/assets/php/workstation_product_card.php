<?php

class WorkstationProductCard
{
    public static function render(array $data = []): string
    {
        $product = $data['product'] ?? [];

        if (empty($product)) {
            return '';
        }

        $productIdAttr = '';
        if (isset($product["name"])) {
            $productIdAttr .= ' id="' . $product["name"] . '"';
        }

        $tagAttr = '';
        if (isset($product["tag"]) && !empty($product["tag"])) {
            $tagAttr .= ' tag="' . strtolower($product["tag"]) . '"';
        }

        $productImage = '';
        if (isset($product["img_src"]) && !empty($product["img_src"]) && isset($product["img_path"])) {
            $productImage .= render_image($product["img_path"] . $product["img_src"], true, "product-image", true, []);
        } else {
            $productImage .= render_image($GLOBALS['urlPath'] . "content/img/placeholder/img-placeholder.webp", false, "product-image");
        }

        $productTag = '';
        if (isset($product["tag"]) && !empty($product["tag"])) {
            $productTag .= '<span class="tag">';
            $productTag .= $product["tag"];
            $productTag .= '</span>';
        }

        $productName = '';
        if (isset($product["name"]) && !empty($product["name"])) {
            $productName .= '<h3 class="title">';
            $productName .= $product["name"];
            $productName .= '</h3>';
        }

        $productDescription = '';
        if (isset($product["description"]) && !empty($product["description"])) {
            $productDescription .= '<div class="hover">';
            $productDescription .= '<p class="description">';
            $productDescription .= nl2br($product["description"]);
            $productDescription .= '</p>';
            $productDescription .= '</div>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ product_id_attr }}', '{{ tag_attr }}', '{{ product_image }}', '{{ product_tag }}', '{{ product_name }}', '{{ product_description }}'],
            [$productIdAttr, $tagAttr, $productImage, $productTag, $productName, $productDescription],
            $template
        );
    }
}
