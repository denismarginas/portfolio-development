<?php

class WorkstationHeader
{
    public static function render(array $data): string
    {
        $post_data = $data['postData'] ?? [];
        $layout = $data['layout'] ?? null;

        if (empty($post_data)) {
            return '';
        }

        $imgImgPath = ($post_data["path_img"] ?? "") . "/";
        $imgMediaPath = ($post_data["media_path"] ?? "") . "/";
        $imgPath = $GLOBALS['urlPath'] . "src/content/img/" . $imgImgPath . $imgMediaPath;

        $workstationSetupImg = false;
        if (isset($post_data["images"]["full_setup"][0]) && !empty($post_data["images"]["full_setup"][0])) {
            $workstationSetupImg = $post_data["images"]["full_setup"][0];
        }

        $textContent = '<h2 data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.6s">' . htmlspecialchars($post_data["title"]) . '</h2>';
        $textContent .= '<p data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s">' . htmlspecialchars($post_data["description"]) . '</p>';

        $visualContent = '';
        if ($workstationSetupImg) {
            $visualContent .= render_image($imgPath . $workstationSetupImg, true);
        } else {
            $visualContent .= svg_get("workstation");
        }

        $statusContent = '';
        if (isset($post_data["status"]) && !empty($post_data["status"])) {
            $statusContent .= '<div class="status">';
            $statusContent .= '<span class="dot ' . htmlspecialchars($post_data["status"]) . '"></span>';
            $statusContent .= '<span>' . htmlspecialchars($post_data["status"]) . '</span>';
            $statusContent .= '</div>';
        }

        $wavesContent = '';
        if (!empty($layout) && $layout == "default") {
            $wavesContent .= '<svg class="wave-shape primary" viewBox="0 0 1440 320">';
            $wavesContent .= '<path fill="var(--w-color-primary)" fill-opacity="1" d="M0,192L60,192C120,192,240,192,360,213.3C480,235,600,277,720,272C840,267,960,213,1080,208C1200,203,1320,245,1380,266.7L1440,288L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>';
            $wavesContent .= '</svg>';
            $wavesContent .= '<svg class="wave-shape secondary" viewBox="0 0 1440 320">';
            $wavesContent .= '<path fill="var(--w-color-secondary)" fill-opacity="1" d="M0,224L80,202.7C160,181,320,139,480,138.7C640,139,800,181,960,176C1120,171,1280,117,1360,90.7L1440,64L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>';
            $wavesContent .= '</svg>';
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ text_content }}', '{{ visual_content }}', '{{ status_content }}', '{{ waves_content }}'],
            [$textContent, $visualContent, $statusContent, $wavesContent],
            $template
        );
    }
}
