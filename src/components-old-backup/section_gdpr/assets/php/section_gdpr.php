<?php

class SectionGdpr
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $jsonGDPR = $data['gdprData'] ?? get_data_json('data_content_personal', 'data')["privacy_policy"];

        $gdprContent = '';
        if (isset($jsonGDPR)) {
            if (isset($jsonGDPR["title"])) {
                $gdprContent .= '<h2>' . $jsonGDPR["title"] . '</h2>';
            }
            if (isset($jsonGDPR["content"])) {
                foreach ($jsonGDPR["content"] as $content) {
                    if (isset($content["subtitle"])) {
                        $gdprContent .= '<h3>' . $content["subtitle"] . '</h3>';
                    }
                    if (isset($content["text"])) {
                        $gdprContent .= '<p>' . $content["text"] . '</p>';
                    }
                }
            }
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ gdpr_content }}', $gdprContent, $template);
    }
}
