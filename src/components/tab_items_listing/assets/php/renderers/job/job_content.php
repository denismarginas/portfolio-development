<?php

class tab_items_listing_job_content
{
    public static function render_content_jobs(array $item, array $data, string $itemId = ''): string
    {
        $itemData = is_array($item['data'] ?? null) ? $item['data'] : $item;
        $seo = $itemData['seo'] ?? [];
        $date = $itemData['date'] ?? [];
        $experience = $itemData['experience'] ?? [];
        $media = $itemData['media'] ?? [];
        $logo = $media['logo'] ?? [];
        $name = (string) ($seo['title'] ?? '');
        $type = (string) ($experience['type'] ?? '');
        $location = implode(', ', tab_items_listing_utility::as_list($experience['location_types'] ?? null));
        $functions = tab_items_listing_utility::as_list($experience['functions'] ?? null);
        $attributes = tab_items_listing_utility::as_list($experience['attributes'] ?? null);
        $workTimeTypes = is_array($experience['work_time_types'] ?? null) ? $experience['work_time_types'] : [];
        $links = is_array($itemData['external_links'] ?? null) ? $itemData['external_links'] : [];
        $dateLabel = tab_items_listing_education_date::education_date_range($date);
        $logoHtml = tab_items_listing_education::render_education_logo($logo, $name);

        $typeTagHtml = '';
        if ($type !== '') {
            $typeTagHtml = tab_items_listing_loader::load_education_partial('type.html', [
                'type' => htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
                'type_svg' => PlatformComponentRenderer::render('svg', [
                    'icon' => 'work_case',
                    'class' => 'tab-items-listing-type-icon',
                ]),
            ]);
        }

        $metaHtml = tab_items_listing_loader::load_education_partial('meta.html', [
            'type_tag' => $typeTagHtml,
            'date_icon' => $dateLabel !== '' ? PlatformComponentRenderer::render('svg', [
                'icon' => 'time',
                'class' => 'tab-items-listing-date-icon',
            ]) : '',
            'date' => htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8'),
            'location_icon' => $location !== '' ? PlatformComponentRenderer::render('svg', [
                'icon' => 'location',
                'class' => 'tab-items-listing-location-icon',
            ]) : '',
            'location' => htmlspecialchars($location, ENT_QUOTES, 'UTF-8'),
        ]);

        $professionHtml = !empty($functions)
            ? tab_items_listing_loader::load_education_partial('profession.html', [
                'profession' => htmlspecialchars(implode(', ', $functions), ENT_QUOTES, 'UTF-8'),
            ])
            : '';

        $attributesHtml = '';
        if (!empty($attributes)) {
            $attributeSvg = PlatformComponentRenderer::render('svg', [
                'icon' => 'chevron-right',
                'class' => 'tab-items-listing-discipline-icon',
            ]);
            $items = '';
            foreach ($attributes as $attribute) {
                $items .= tab_items_listing_loader::load_education_partial('discipline_item.html', [
                    'discipline' => htmlspecialchars((string) $attribute, ENT_QUOTES, 'UTF-8'),
                    'svg' => $attributeSvg,
                ]);
            }
            $attributesHtml = tab_items_listing_loader::load_education_partial('disciplines.html', [
                'items' => $items,
                'heading' => '',
            ]);
        }

        $worktimeHtml = tab_items_listing_job_worktime::render_worktime_types($workTimeTypes);
        $linksHtml = tab_items_listing_education::render_education_links($links, $data);
        $linksContainerHtml = $linksHtml !== '' ? tab_items_listing_loader::load_education_partial('links_container.html', ['links_html' => $linksHtml, 'projects_html' => '']) : '';
        $footerHtml = ($worktimeHtml !== '' || $linksContainerHtml !== '') ? '<div class="tab-items-listing-footer">' . $worktimeHtml . $linksContainerHtml . '</div>' : '';
        $durationHtml = tab_items_listing_job_duration::render_job_duration($date);
        $display = (string) ($data['display'] ?? 'detailed');

        return tab_items_listing_loader::load_job_partial('template.html', [
            'item_id' => htmlspecialchars($itemId, ENT_QUOTES, 'UTF-8'),
            'display' => htmlspecialchars($display, ENT_QUOTES, 'UTF-8'),
            'pattern_svg' => PlatformComponentRenderer::render('svg', [
                'icon' => 'dotted-signal-pattern',
                'class' => 'tab-items-listing-pattern-svg',
            ]),
            'logo_html' => $logoHtml,
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'meta_html' => $metaHtml,
            'profession_html' => $professionHtml,
            'disciplines_html' => $attributesHtml,
            'duration_html' => $durationHtml,
            'footer_html' => $footerHtml,
        ]);
    }
}
