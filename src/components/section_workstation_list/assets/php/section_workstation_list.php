<?php

class SectionWorkstationList
{
    public static function render(array $data = []): string
    {
        $globalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $workstationList = $data['workstationList'] ?? get_data_json('data_post_workstations', 'data');
        $urlPath = $GLOBALS['urlPath'];

        $workstationItems = '';

        foreach ($workstationList as $workstation) {
            $styles = '';
            if (isset($workstation["style"]) && !empty($workstation["style"])) {
                foreach ($workstation["style"] as $styleKey => $styleValue) {
                    $styles .= $styleKey . ': ' . $styleValue . ';';
                }
            } else {
                $styles = '--w-color-primary: var( --dm-color-primary );
                   --w-color-secondary: var( --dm-color-secondary );
                   --w-text-color-on-bg: var( --dm-color-white );
                   --w-title-font:  var( --dm-font-family-secondary );
                   --w-text-font: var( --dm-font-family-primary );';
            }

            $workstationItems .= '<li class="dm-workstation-item" style="' . $styles . '" data-motion="transition-fade-0" data-duration="0.8s">';

            $workstationSetupImg = false;
            $workstationDeviceImg = false;
            $imgImgPath = ($workstation["path_img"] ?? "") . "/";
            $imgMediaPath = ($workstation["media_path"] ?? "") . "/";
            $imgPath = $urlPath . "content/img/" . $imgImgPath . $imgMediaPath;

            if (isset($workstation["images"]["full_setup"][0]) && !empty($workstation["images"]["full_setup"][0])) {
                $workstationSetupImg = $workstation["images"]["full_setup"][0];
            }

            if (isset($workstation["images"]["workstation"][0]) && !empty($workstation["images"]["workstation"][0])) {
                $workstationDeviceImg = $workstation["images"]["workstation"][0];
            }

            if (isset($workstation["file"]) && !empty($workstation["file"])) {
                $workstationItems .= '<a class="heading" href="' . $workstation["file"] . $globalData["page_slug_extension"] . '">';
            }

            $workstationItems .= '<div class="preview" data-motion="transition-slideInBottom-0" data-duration="0.4s">';
            $workstationItems .= '<div class="primary" data-motion="transition-fade-0 transition-blur-0" data-duration="0.8s" data-delay="0.5s">';
            if ($workstationSetupImg) {
                $workstationItems .= render_image($imgPath . $workstationSetupImg);
            } else {
                $workstationItems .= svg_get("workstation");
            }
            $workstationItems .= '</div>';

            if (isset($workstation["status"]) && !empty($workstation["status"])) {
                $workstationItems .= '<div class="status"><span class="dot ' . $workstation["status"] . '"></span><span>' . $workstation["status"] . '</span></div>';
            }
            $workstationItems .= '</div>';

            if (isset($workstation["file"]) && !empty($workstation["file"])) {
                $workstationItems .= '</a>';
            }

            if (isset($workstation["title"]) && !empty($workstation["title"])) {
                $workstationItems .= '<h3 class="title">';
                if (isset($workstation["file"]) && !empty($workstation["file"])) {
                    $workstationItems .= '<a class="heading" href="' . $workstation["file"] . $globalData["page_slug_extension"] . '">' . $workstation["title"] . '</a>';
                } else {
                    $workstationItems .= '<span class="heading">' . $workstation["title"] . '</span>';
                }
                $workstationItems .= '</h3>';
            }

            $workstationItems .= '</li>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ workstation_items }}', $workstationItems, $template);
    }
}
