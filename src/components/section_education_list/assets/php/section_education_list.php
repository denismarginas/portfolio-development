<?php

class SectionEducationList
{
    public static function render(array $data): string
    {
        $educations = $data['educations'] ?? get_data_json('data_items_education', 'data');
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $projectsData = $data['projectsData'] ?? get_data_json('data_post_projects', 'data');

        $compactList = '';
        foreach ($educations as $index => $item) {
            $compactList .= '<li class="education" data-motion="transition-fade-0" data-duration="0.6s" data-index="' . $index . '">';
            $compactList .= '<a href="#' . strtolower(str_replace(" ", "-", $item["name"])) . '" class="logo" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.3s"';
            if ($item["img_bg"] == "dark") {
                $compactList .= " data-layout='dark' data-animation='shine'";
            } elseif ($item["img_bg"] == "light") {
                $compactList .= " data-layout='light' data-animation='shine-gray'";
            }
            $compactList .= '>';
            $compactList .= render_image($GLOBALS['urlPath'] . $item["img"]);
            $compactList .= '</a>';
            $compactList .= '<a href="#' . strtolower(str_replace(" ", "-", $item["name"])) . '" class="name" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.4s">';
            $compactList .= '<span>' . $item["name"] . '</span>';
            $compactList .= '</a>';
            $compactList .= '</li>';
            if ($index < count($educations) - 1) {
                $compactList .= '<li class="gap-line" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.4s"></li>';
            }
        }

        $detailedList = '';
        $j = 1;
        foreach ($educations as $education) {
            $detailedList .= '<li class="education" id="' . strtolower(str_replace(" ", "-", $education["name"])) . '" data-motion="transition-fade-0" data-duration="0.6s" data-delay="' . ($j * 0.02) . 's">';
            if (isset($education["img"]) && !empty($education["img"])) {
                $detailedList .= '<div class="logo"';
                if ($education["img_bg"] == "dark") {
                    $detailedList .= " data-layout='dark'";
                } elseif ($education["img_bg"] == "light") {
                    $detailedList .= " data-layout='light'";
                }
                $detailedList .= ' data-motion="transition-fade-0" data-duration="1.1s">';
                $detailedList .= render_image($GLOBALS['urlPath'] . $education["img"], false, false, true, ["data-motion" => "transition-blur-0", "data-duration" => "0.5s"]);
                $detailedList .= '</div>';
            }
            $detailedList .= '<div class="details">';
            $detailedList .= '<div class="head" data-motion="transition-fade-0" data-duration="0.5s">';
            if (isset($education["name"]) && !empty($education["name"])) {
                $detailedList .= '<h2 class="name" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.9s">' . $education["name"] . '</h2>';
            }
            if (isset($education["dates"]) && !empty($education["dates"])) {
                $detailedList .= '<ul class="dates" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s">';
                $detailedList .= '<li>' . $education["dates"]["date_start"] . '</li>';
                $detailedList .= '<li>' . $education["dates"]["date_end"] . '</li>';
                $detailedList .= '</ul>';
            }
            if (isset($education["type"]) && !empty($education["type"])) {
                $detailedList .= '<span class="type" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.9s">';
                ob_start();
                svg_render('education');
                $detailedList .= ob_get_clean();
                $detailedList .= '<span>' . $education["type"] . '</span>';
                $detailedList .= '</span>';
            }
            if (isset($education["profession"]) && !empty($education["profession"])) {
                $detailedList .= '<div class="profession-list">';
                foreach ($education["profession"] as $profession) {
                    $detailedList .= '<span class="profession" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.8s">' . $profession . '</span>';
                }
                $detailedList .= '</div>';
            }
            $detailedList .= '</div>';
            $detailedList .= '<div class="description" data-motion="transition-fade-0" data-duration="0.55s">';
            if (isset($education["description"]["text"]) && !empty($education["description"]["text"])) {
                $detailedList .= '<p class="text" data-motion="transition-fade-0" data-duration="0.7s">' . $education["description"]["text"] . '</p>';
            }
            if (isset($education["description"]["list"]) && !empty($education["description"]["list"])) {
                $detailedList .= '<ul class="list" data-motion="transition-fade-0" data-duration="1.1s">';
                $i = 1;
                foreach ($education["description"]["list"] as $attributes) {
                    $detailedList .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="' . ($i * 0.03 + 0.4) . 's" data-delay="' . ($i * 0.01) . 's">';
                    ob_start();
                    svg_render('chevron-right');
                    $detailedList .= ob_get_clean();
                    $detailedList .= '<span>' . $attributes . '</span>';
                    $detailedList .= '</li>';
                    $i++;
                }
                $detailedList .= '</ul>';
            }
            $detailedList .= '</div>';
            if (isset($education["external_links"]) || isset($education["projects"])) {
                $detailedList .= '<div class="additional-information">';
                if (isset($education["external_links"]) && !empty($education["external_links"])) {
                    $detailedList .= '<div class="external-links" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.7s">';
                    foreach ($education["external_links"] as $item) {
                        $detailedList .= '<a class="external-link" href="' . add_https($item["link"]) . '" target="_blank">';
                        ob_start();
                        svg_render($item["icon"]);
                        $detailedList .= ob_get_clean();
                        $detailedList .= '<span>' . remove_https($item["link"]) . '</span>';
                        $detailedList .= '</a>';
                    }
                    $detailedList .= '</div>';
                }
                if (isset($education["projects"]) && !empty($education["projects"])) {
                    $detailedList .= '<div class="projects" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.8s">';
                    foreach ($education["projects"] as $project) {
                        if (isset($project["title"])) {
                            $match = false;
                            foreach ($projectsData as $project_item) {
                                $projectTitle = $project_item['data']['title'] ?? '';
                                if ($projectTitle === $project["title"]) {
                                    $match = true;
                                    $detailedList .= '<a class="project" href="' . $project_item["post_id"] . '" target="_blank">';
                                    if (isset($project["icon"])) {
                                        ob_start();
                                        svg_render($project["icon"]);
                                        $detailedList .= ob_get_clean();
                                    }
                                    $detailedList .= '<span>' . $projectTitle . '</span>';
                                    $detailedList .= '</a>';
                                }
                            }
                            if (!$match) {
                                $detailedList .= '<div class="project" target="_blank">';
                                if (isset($project["icon"])) {
                                    ob_start();
                                    svg_render($project["icon"]);
                                    $detailedList .= ob_get_clean();
                                }
                                $detailedList .= '<span>' . $project["title"] . '</span>';
                                $detailedList .= '</div>';
                            }
                        }
                    }
                    $detailedList .= '</div>';
                }
                $detailedList .= '</div>';
            }
            $detailedList .= '</div>';
            $detailedList .= '<div class="background-shape" data-motion="transition-fade-0" data-duration="0.5s" data-delay="0.5s">';
            ob_start();
            svg_render('background-shape-1');
            $detailedList .= ob_get_clean();
            $detailedList .= '</div>';
            $detailedList .= '</li>';
            $j++;
        }

        return self::render_template([
            'compact_list' => $compactList,
            'detailed_list' => $detailedList,
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
