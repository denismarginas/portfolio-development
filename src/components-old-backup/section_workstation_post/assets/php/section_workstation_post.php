<?php

class SectionWorkstationPost
{
    public static function render(array $data = []): string
    {
        $postData = $data['postCurrentData'] ?? [];
        $layout = $data['layout'] ?? '';

        if (empty($postData)) {
            return '';
        }

        $layoutAttr = !empty($layout) ? ' data-layout="' . $layout . '"' : '';

        $styles = '';
        if (isset($postData["style"]) && !empty($postData["style"])) {
            foreach ($postData["style"] as $styleKey => $styleValue) {
                $styles .= $styleKey . ': ' . $styleValue . ';';
            }
        } else {
            $styles = '--w-color-primary: var( --dm-color-primary );
                   --w-color-secondary: var( --dm-color-secondary );
                   --w-text-color-on-bg: var( --dm-color-white );
                   --w-title-font:  var( --dm-font-family-secondary );
                   --w-text-font: var( --dm-font-family-primary );';
        }

        $content = '';
        if (isset($postData["content"]) && is_array($postData["content"])) {
            foreach ($postData["content"] as $postContentElement) {
                $content .= '<li class="element">';
                if (is_array($postContentElement)) {
                    $elementData = [];
                    foreach ($postContentElement as &$element) {
                        if ($element === "post_data") {
                            $element = ['post_data' => $postData];
                        }
                    }
                    $componentName = $postContentElement[0];
                    $componentParams = $postContentElement[1] ?? [];
                    if ($componentParams === "post_data") {
                        $componentParams = ['post_data' => $postData];
                    }
                    $content .= render_component($componentName, is_array($componentParams) ? $componentParams : []);
                } else {
                    $content .= render_component($postContentElement, ['post_data' => $postData]);
                }
                $content .= '</li>';
            }
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ layout_attr }}', '{{ styles }}', '{{ content }}'],
            [$layoutAttr, $styles, $content],
            $template
        );
    }
}