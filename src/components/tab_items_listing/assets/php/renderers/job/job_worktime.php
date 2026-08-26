<?php

class tab_items_listing_job_worktime
{
    public static function render_worktime_types(array $workTimeTypes): string
    {
        if (empty($workTimeTypes)) return '';
        $items = '';
        foreach ($workTimeTypes as $wt) {
            $name = htmlspecialchars((string) ($wt['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $hours = htmlspecialchars((string) ($wt['hours'] ?? ''), ENT_QUOTES, 'UTF-8');
            $date = tab_items_listing_education_date::education_date_range(['start' => $wt['start'] ?? '', 'end' => $wt['end'] ?? '']);
            $items .= tab_items_listing_loader::load_job_partial('worktime_type.html', ['name' => $name, 'hours' => $hours, 'date' => htmlspecialchars($date, ENT_QUOTES, 'UTF-8')]);
        }
        if ($items === '') return '';
        return tab_items_listing_loader::load_job_partial('worktime_types_container.html', ['items' => $items]);
    }
}