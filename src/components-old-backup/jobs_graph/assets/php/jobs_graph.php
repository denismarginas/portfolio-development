<?php

class JobsGraph
{
    public static function render(array $data = []): string
    {
        $jobs = $data['jobs'] ?? get_data_json('data_items_jobs', 'data');

        $earliestStartDate = null;
        $latestEndDate = null;

        foreach ($jobs as $job):
            if ($job["display"] != "true"):
                continue;
            endif;

            $startDate = DateTime::createFromFormat('d.m.Y', $job['date_start']);
            $endDate = ($job['date_end'] === 'In progress') ? new DateTime() : DateTime::createFromFormat('d.m.Y', $job['date_end']);

            if ($earliestStartDate === null || $startDate < $earliestStartDate):
                $earliestStartDate = $startDate;
            endif;

            if ($latestEndDate === null || $endDate > $latestEndDate):
                $latestEndDate = $endDate;
            endif;
        endforeach;

        $earliestStartDateDisplay = ($earliestStartDate !== null) ? $earliestStartDate->format('m.Y') : 'N/A';
        $latestEndDateDisplay = ($latestEndDate !== null) ? $latestEndDate->format('m.Y') : 'In progress';

        $workTimeline = '';
        $workTimeline .= '<div class="work-timeline">';

        if ($earliestStartDate !== null && $latestEndDate !== null):
            $startYear = (int) $earliestStartDate->format('Y');
            $endYear = (int) $latestEndDate->format('Y');

            for ($currentYear = $startYear; $currentYear <= $endYear; $currentYear++):
                $workTimeline .= '<div class="year">';
                $workTimeline .= '<span>' . $currentYear . '</span>';

                for ($month = 1; $month <= 12; $month++):
                    $currentDate = new DateTime("$currentYear-$month-01");

                    $workTimeline .= '<div class="month">';
                    $workTimeline .= '<span>' . $currentDate->format('M.Y') . '</span>';
                    $workTimeline .= '</div>';
                endfor;

                $workTimeline .= '</div>';
            endfor;
        endif;

        $workTimeline .= '</div>';

        $jobsTimeline = '';
        $jobsTimeline .= '<div class="jobs-timeline">';

        if ($earliestStartDate !== null && $latestEndDate !== null):
            $startYear = (int) $earliestStartDate->format('Y');
            $endYear = (int) $latestEndDate->format('Y');

            for ($currentYear = $startYear; $currentYear <= $endYear; $currentYear++):
                $jobsTimeline .= '<div class="year">';
                $jobsTimeline .= '<span>' . $currentYear . '</span>';

                for ($month = 1; $month <= 12; $month++):
                    $currentDate = new DateTime("$currentYear-$month-01");
                    $daysWorkedInMonth = calculateDaysWorkedInMonth($currentDate, $jobs);

                    if ($daysWorkedInMonth >= 28):
                        $jobsTimeline .= '<div class="month worked">';
                    else:
                        $jobsTimeline .= '<div class="month">';
                    endif;

                    $jobsTimeline .= '<span>' . $currentDate->format('M.Y') . '</span>';
                    $jobsTimeline .= '</div>';
                endfor;

                $jobsTimeline .= '</div>';
            endfor;
        endif;

        $jobsTimeline .= '</div>';

        $total_experience_years = 0;
        $total_experience_months = 0;
        $total_timeline_days = 0;

        $job_periods = array();

        foreach ($jobs as $job):
            if ($job["display"] != "true"):
                continue;
            endif;

            $startDate = DateTime::createFromFormat('d.m.Y', $job["date_start"]);
            $endDate = ($job["date_end"] === "In progress" || $job["date_end"] === "Working")
                ? new DateTime()
                : DateTime::createFromFormat('d.m.Y', $job["date_end"]);

            $experience_interval = $startDate->diff($endDate);
            $experience_years = $experience_interval->y;
            $experience_months = $experience_interval->m;

            $total_experience_years += $experience_years;
            $total_experience_months += $experience_months;

            $job_periods[] = array('start' => $startDate, 'end' => $endDate);
        endforeach;

        foreach ($job_periods as $key => $period):
            $total_timeline_interval = $period['start']->diff($period['end']);
            $total_timeline_days += $total_timeline_interval->days;

            for ($i = $key + 1; $i < count($job_periods); $i++):
                $overlap_start = max($period['start'], $job_periods[$i]['start']);
                $overlap_end = min($period['end'], $job_periods[$i]['end']);

                if ($overlap_start < $overlap_end):
                    $overlap_interval = $overlap_start->diff($overlap_end);
                    $total_timeline_days -= $overlap_interval->days;
                endif;
            endfor;
        endforeach;

        $total_experience_years += floor($total_experience_months / 12);
        $total_experience_months = $total_experience_months % 12;

        $total_timeline_years = floor($total_timeline_days / 365);
        $total_timeline_months = floor(($total_timeline_days % 365) / 30);

        $experienceSummary = '';

        if ($total_experience_years > 0 || $total_experience_months > 0):
            $experienceSummary .= '<p>';
            $experienceSummary .= '<span>Total Work Experience: </span>';
            $experienceSummary .= '<span>' . $total_experience_years . '</span>';
            $experienceSummary .= '<span> years, </span>';
            $experienceSummary .= '<span>' . $total_experience_months . '</span>';
            $experienceSummary .= '<span> months</span>';
            $experienceSummary .= '</p>';
        endif;

        if ($total_timeline_years > 0 || $total_timeline_months > 0):
            $experienceSummary .= '<p>';
            $experienceSummary .= '<span>Total Work Timeline: </span>';
            $experienceSummary .= '<span>' . $total_timeline_years . '</span>';
            $experienceSummary .= '<span> years, </span>';
            $experienceSummary .= '<span>' . $total_timeline_months . '</span>';
            $experienceSummary .= '<span> months</span>';
            $experienceSummary .= '</p>';
        endif;

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace(
            ['{{ work_timeline }}', '{{ jobs_timeline }}', '{{ experience_summary }}'],
            [$workTimeline, $jobsTimeline, $experienceSummary],
            $template
        );
    }
}
