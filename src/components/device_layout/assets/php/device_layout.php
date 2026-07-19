<?php

class DeviceLayout
{
    public static function render(array $data = []): string
    {
        $postData = $data['post_current_data'] ?? $data['post_data'] ?? [];
        $deviceType = $data['device'] ?? $data['type'] ?? 'desktop';
        $urlPath = DataService::get_url_path();

        $personal = DataService::get_personal_data();
        $deviceData = $personal['post_projects']['img'] ?? [];

        $modelKey = $deviceType . '-model-01';
        $deviceModel1 = $deviceData['devices'][$modelKey] ?? '';
        $deviceModel2 = $deviceData['devices'][$deviceType . '-model-02'] ?? '';
        $deviceModel3 = $deviceData['devices'][$deviceType . '-model-03'] ?? '';

        $deviceMode = '';

        $year = null;
        if (isset($postData['date']['date_start'])) {
            preg_match('/\d{4}/', $postData['date']['date_start'], $matches);
            $year = $matches[0] ?? null;
        }

        $projectTypes = $postData['project']['types'] ?? $postData['project_types'] ?? [];
        $isPersonal = in_array('personal', $projectTypes) && !in_array("bachelor's thesis", $projectTypes);

        if ($isPersonal && !empty($deviceModel1)) {
            $deviceMode = $deviceModel1;
        } elseif ($year) {
            if ($year < 2022 && !empty($deviceModel2)) {
                $deviceMode = $deviceModel2;
            } elseif ($year >= 2022 && !empty($deviceModel3)) {
                $deviceMode = $deviceModel3;
            }
        } elseif (!empty($deviceModel3)) {
            $deviceMode = $deviceModel3;
        }

        $content = '';
        if (!empty($deviceMode)) {
            $content .= ComponentRenderer::render_component('image', [
                'src' => $urlPath . $deviceMode,
                'class' => 'device',
                'lazy' => true,
            ]);
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace('{{ content }}', $content, $template);
    }
}