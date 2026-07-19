<?php

class SectionJobList
{
    public static function render(array $data): string
    {
        $jsonGlobalData = $data['globalData'] ?? get_data_json('data_global_settings', 'data');
        $jobs = $data['jobs'] ?? get_data_json('data_items_jobs', 'data');

        $theme_path = $GLOBALS['urlPath'] . $jsonGlobalData["themes_path"] . "/" . $jsonGlobalData["theme_active"]["dir_name"];

        $themeScripts = '';
        if (!empty($theme_path)) {
            $themeScripts = '<script src="' . $theme_path . '/js/content-posts-jobs-worktime-calculation.js"></script>';
            $themeScripts .= '<script src="' . $theme_path . '/js/content-posts-jobs-worktime-graph-calculation.js"></script>';
        }

        $jobs_list = [];
        $show_all_jobs = true;

        foreach ($jobs as $job) {
            if ($job["display"] == "true") {
                $jobs_list[] = $job;
            } else {
                $show_all_jobs = false;
            }
        }

        $filteredJobsCompact = '';
        if (count($jobs_list) > 0) {
            $filteredJobsCompact = '<li class="dm-job-list" data-motion="transition-fade-0" data-duration="0.6s">';
            foreach ($jobs_list as $job) {
                $filteredJobsCompact .= '<ul data-listing="' . count($jobs_list) . '">';
                $filteredJobsCompact .= '<li';
                if ($job["img_bg"] == "dark") {
                    $filteredJobsCompact .= " data-layout='dark' data-animation='shine'";
                } elseif ($job["img_bg"] == "light") {
                    $filteredJobsCompact .= " data-layout='light' data-animation='shine-gray'";
                }
                $filteredJobsCompact .= '>';
                $filteredJobsCompact .= '<a href="#' . strtolower(str_replace(" ", "-", $job["name"])) . '" class="work-logo" data-motion="transition-fade-0 transition-grow-0" data-duration="0.8s">';
                $filteredJobsCompact .= render_image($GLOBALS['urlPath'] . $job["img"]);
                $filteredJobsCompact .= '</a>';
                $filteredJobsCompact .= '<a href="#' . strtolower(str_replace(" ", "-", $job["name"])) . '" class="company-name" data-motion="transition-fade-0" data-duration="1s">';
                $filteredJobsCompact .= '<span>' . $job["name"] . '</span>';
                $filteredJobsCompact .= '</a>';
                $filteredJobsCompact .= '</li>';
                $filteredJobsCompact .= '</ul>';
            }
            $filteredJobsCompact .= render_component('animation_waves', $data);
            $filteredJobsCompact .= '</li>';
        }

        $filteredJobsDetailed = '';
        $j = 1;
        foreach ($jobs_list as $job) {
            $filteredJobsDetailed .= '<li class="dm-job" id="' . strtolower(str_replace(" ", "-", $job["name"])) . '" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.4s" data-delay="' . ($j * 0.05) . 's">';
            $filteredJobsDetailed .= '<ul>';
            $filteredJobsDetailed .= '<li class="work-summary"';
            if ($job["img_bg"] == "dark") {
                $filteredJobsDetailed .= " data-layout='dark'";
            } elseif ($job["img_bg"] == "light") {
                $filteredJobsDetailed .= " data-layout='light'";
            }
            $filteredJobsDetailed .= ' data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1.1s">';
            $filteredJobsDetailed .= '<div class="work-logo" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.6s">';
            $filteredJobsDetailed .= render_image($GLOBALS['urlPath'] . $job["img"]);
            $filteredJobsDetailed .= '</div>';
            $filteredJobsDetailed .= '<h2 class="company-name" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.9s">' . $job["name"] . '</h2>';
            $filteredJobsDetailed .= '<ul class="work-dates" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s">';
            $filteredJobsDetailed .= '<li>' . $job["date_start"] . '</li>';
            $filteredJobsDetailed .= '<li>' . $job["date_end"] . '</li>';
            $filteredJobsDetailed .= '</ul>';
            $filteredJobsDetailed .= '</li>';
            $filteredJobsDetailed .= '<li class="work-details" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.7s">';
            $filteredJobsDetailed .= '<h3 class="work-function">';
            $filteredJobsDetailed .= '<span data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s">Work Function:</span>';
            foreach ($job["function"] as $work_function) {
                $filteredJobsDetailed .= '<span class="function" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="0.8s">' . $work_function . '</span>';
            }
            $filteredJobsDetailed .= '</h3>';
            $filteredJobsDetailed .= '<ul class="work-attributes">';
            $i = 1;
            foreach ($job["work_attributes"] as $work_attributes) {
                $filteredJobsDetailed .= '<li class="work-attribute" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="' . ($i * 0.2) . 's" data-delay="' . ($i * 0.06) . 's">';
                ob_start();
                svg_render('chevron-right');
                $filteredJobsDetailed .= ob_get_clean();
                $filteredJobsDetailed .= '<span>' . $work_attributes . '</span>';
                $filteredJobsDetailed .= '</li>';
                $i++;
            }
            $filteredJobsDetailed .= '</ul>';
            $filteredJobsDetailed .= '<ul class="work-data">';
            if (strtotime($job["date_start"]) !== false && strtotime($job["date_end"]) !== false) {
                $startDate = DateTime::createFromFormat('d.m.Y', $job["date_start"]);
                $endDate = DateTime::createFromFormat('d.m.Y', $job["date_end"]);
                $interval = $startDate->diff($endDate);
                $years = $interval->y;
                $months = $interval->m;
                $days = $interval->d;
                if ($days > 28) {
                    $months++;
                }
                if ($months > 0 or $years > 0) {
                    $filteredJobsDetailed .= '<li>';
                    $filteredJobsDetailed .= '<ul class="work-time">';
                    $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1s" data-delay="0.1s">';
                    $filteredJobsDetailed .= '<p>Work Time:</p>';
                    $filteredJobsDetailed .= '</li>';
                    if ($years != 0) {
                        $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.2s">';
                        $filteredJobsDetailed .= "<span class='nr-circle years-nr'>" . $years . "</span>";
                        if ($years > 1) {
                            $filteredJobsDetailed .= "<span class='time-text'>Years</span>";
                        } else {
                            $filteredJobsDetailed .= "<span class='time-text'>Year</span>";
                        }
                        $filteredJobsDetailed .= '</li>';
                    }
                    if ($months != 0) {
                        $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.3s">';
                        $filteredJobsDetailed .= "<span class='nr-circle months-nr'>" . $months . "</span>";
                        if ($months > 1) {
                            $filteredJobsDetailed .= "<span class='time-text'>Months</span>";
                        } else {
                            $filteredJobsDetailed .= "<span class='time-text'>Month</span>";
                        }
                        $filteredJobsDetailed .= '</li>';
                    }
                    $filteredJobsDetailed .= '</ul>';
                    $filteredJobsDetailed .= '</li>';
                }
            }
            if (isset($job["work_time_type"])) {
                $filteredJobsDetailed .= '<li>';
                $filteredJobsDetailed .= '<ul class="work_time_type">';
                foreach ($job["work_time_type"] as $work_time_type) {
                    $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1.2s">';
                    if (isset($work_time_type["name"])) {
                        $filteredJobsDetailed .= '<span class="work-name" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.7s">' . $work_time_type["name"] . '</span>';
                    }
                    if (isset($work_time_type["hours"])) {
                        $filteredJobsDetailed .= '<span class="work-hours" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.8s">' . $work_time_type["hours"] . '</span>';
                    }
                    if (isset($work_time_type["date_start"]) and isset($work_time_type["date_end"])) {
                        $filteredJobsDetailed .= '<ul class="work-dates" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.9s">';
                        $filteredJobsDetailed .= '<li>' . $work_time_type["date_start"] . '</li>';
                        $filteredJobsDetailed .= '<li> - </li>';
                        $filteredJobsDetailed .= '<li>' . $work_time_type["date_end"] . '</li>';
                        $filteredJobsDetailed .= '</ul>';
                    }
                    $filteredJobsDetailed .= '</li>';
                }
                $filteredJobsDetailed .= '</ul>';
                $filteredJobsDetailed .= '</li>';
            }
            if (isset($job["work_location_type"])) {
                $filteredJobsDetailed .= '<li>';
                $filteredJobsDetailed .= '<ul class="work_location_type">';
                foreach ($job["work_location_type"] as $work_location_type) {
                    $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInRight-0" data-duration="1s">' . $work_location_type . '</li>';
                }
                $filteredJobsDetailed .= '</ul>';
                $filteredJobsDetailed .= '</li>';
            }
            $filteredJobsDetailed .= '</ul>';
            $filteredJobsDetailed .= '<ul class="work-socials" data-motion="transition-fade-0 transition-slideInRight-0" data-duration="0.8s">';
            if (isset($job["url_web"]) && !empty($job["url_web"])) {
                $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.3s">';
                $filteredJobsDetailed .= '<a href="' . add_https($job["url_web"]) . '" target="_blank">';
                ob_start();
                svg_render('web');
                $filteredJobsDetailed .= ob_get_clean();
                $filteredJobsDetailed .= '</a></li>';
            }
            if (isset($job["url_facebook"]) && !empty($job["url_facebook"])) {
                $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.3s">';
                $filteredJobsDetailed .= '<a href="' . add_https($job["url_facebook"]) . '" target="_blank">';
                ob_start();
                svg_render('socials-facebook');
                $filteredJobsDetailed .= ob_get_clean();
                $filteredJobsDetailed .= '</a></li>';
            }
            if (isset($job["url_instagram"]) && !empty($job["url_instagram"])) {
                $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.3s">';
                $filteredJobsDetailed .= '<a href="' . add_https($job["url_instagram"]) . '" target="_blank">';
                ob_start();
                svg_render('socials-instagram');
                $filteredJobsDetailed .= ob_get_clean();
                $filteredJobsDetailed .= '</a></li>';
            }
            if (isset($job["url_linkedin"]) && !empty($job["url_linkedin"])) {
                $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.3s">';
                $filteredJobsDetailed .= '<a href="' . add_https($job["url_linkedin"]) . '" target="_blank">';
                ob_start();
                svg_render('socials-linkedin');
                $filteredJobsDetailed .= ob_get_clean();
                $filteredJobsDetailed .= '</a></li>';
            }
            if (isset($job["url_x"]) && !empty($job["url_x"])) {
                $filteredJobsDetailed .= '<li data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.3s">';
                $filteredJobsDetailed .= '<a href="' . add_https($job["url_x"]) . '" target="_blank">';
                ob_start();
                svg_render('socials-x');
                $filteredJobsDetailed .= ob_get_clean();
                $filteredJobsDetailed .= '</a></li>';
            }
            $filteredJobsDetailed .= '</ul>';
            $filteredJobsDetailed .= '</li>';
            $filteredJobsDetailed .= '</ul>';
            $filteredJobsDetailed .= '</li>';
            if ($j > 5) {
                $j = 5;
            } else {
                $j++;
            }
        }

        $noteHtml = '';
        if (!$show_all_jobs) {
            $noteHtml = '<li class="dm-note" data-motion="transition-fade-0 transition-slideInLeft-0" data-duration="1.2s" data-delay="0.2s">';
            $noteHtml .= '<p id="freelancer" style="display:none !important;"></p>';
            $noteHtml .= '<p id="unspecified">Note: Please be aware that this is not an completed list, and there may be additional professional experiences not mentioned here. If you have specific inquiries about my work history or would like more details, feel free to reach out.</p>';
            $noteHtml .= '</li>';
        }

        $graphHtml = render_component('jobs_graph', $data);

        return self::render_template([
            'theme_scripts' => $themeScripts,
            'filtered_jobs_compact' => $filteredJobsCompact,
            'filtered_jobs_detailed' => $filteredJobsDetailed,
            'note_html' => $noteHtml,
            'graph_html' => $graphHtml,
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
