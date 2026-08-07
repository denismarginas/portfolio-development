<?php

trait card_render_education
{
    protected static function render_education(array $data): string
    {
        $item = $data['post_current_data'] ?? $data['item'] ?? [];
        $display = (string) ($data['display'] ?? 'detailed');

        $itemData = is_array($item['data'] ?? null) ? $item['data'] : $item;
        $seo = $itemData['seo'] ?? [];
        $date = $itemData['date'] ?? [];
        $education = $itemData['education'] ?? [];
        $media = $itemData['media'] ?? [];
        $logo = $media['logo'] ?? [];

        $name = (string) ($seo['title'] ?? '');
        $type = (string) ($education['type'] ?? '');
        $location = (string) ($education['location'] ?? '');
        $profession = self::as_list($education['profession'] ?? null);
        $heading = (string) ($education['heading'] ?? '');
        $disciplinesData = $education['disciplines'] ?? null;
        $disciplines = [];
        $disciplinesSvg = '';
        if (is_array($disciplinesData)) {
            if (isset($disciplinesData['list']) && is_array($disciplinesData['list'])) {
                $disciplines = array_values(array_map('strval', $disciplinesData['list']));
                $disciplinesSvg = (string) ($disciplinesData['svg'] ?? '');
            } else {
                $disciplines = self::as_list($disciplinesData);
            }
        }
        $projects = is_array($education['projects'] ?? null) ? $education['projects'] : [];
        $links = is_array($itemData['external_links'] ?? null) ? $itemData['external_links'] : [];

        $dateLabel = self::education_date_range($date);
        $logoHtml = self::render_education_logo($logo, $name);
        $typeTagHtml = $type !== '' ? self::load_education_partial('type.html', [
            'type' => htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
        ]) : '';

        $metaHtml = self::load_education_partial('meta.html', [
            'type_tag' => $typeTagHtml,
            'date' => htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8'),
            'location' => htmlspecialchars($location, ENT_QUOTES, 'UTF-8'),
        ]);

        $professionHtml = !empty($profession)
            ? self::load_education_partial('profession.html', [
                'profession' => htmlspecialchars(implode(', ', $profession), ENT_QUOTES, 'UTF-8'),
            ])
            : '';

        $disciplinesHtml = '';
        if (!empty($disciplines)) {
            $svgHtml = '';
            if ($disciplinesSvg !== '') {
                $svgHtml = PlatformComponentRenderer::render('svg', [
                    'icon' => $disciplinesSvg,
                    'class' => 'card-education-discipline-icon',
                ]);
            }

            $items = '';
            foreach ($disciplines as $discipline) {
                $items .= self::load_education_partial('discipline_item.html', [
                    'discipline' => htmlspecialchars((string) $discipline, ENT_QUOTES, 'UTF-8'),
                    'svg' => $svgHtml,
                ]);
            }
            $disciplinesHtml = self::load_education_partial('disciplines.html', [
                'items' => $items,
                'heading' => htmlspecialchars($heading, ENT_QUOTES, 'UTF-8'),
            ]);
        }

        $linksHtml = self::render_education_links($links);
        $projectsHtml = self::render_education_projects($projects);

        $linksContainerHtml = '';
        if ($linksHtml !== '' || $projectsHtml !== '') {
            $linksContainerHtml = self::load_education_partial('links_container.html', [
                'links_html' => $linksHtml,
                'projects_html' => $projectsHtml,
            ]);
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template_card_education.html', [
            'display' => htmlspecialchars($display, ENT_QUOTES, 'UTF-8'),
            'logo_html' => $logoHtml,
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'meta_html' => $metaHtml,
            'profession_html' => $professionHtml,
            'disciplines_html' => $disciplinesHtml,
            'links_container_html' => $linksContainerHtml,
        ]);
    }

    protected static function load_education_partial(string $name, array $vars): string
    {
        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/parts/education/' . $name, $vars);
    }

    protected static function render_education_logo(array $logo, string $alt): string
    {
        $src = (string) ($logo['img'] ?? '');
        if ($src === '') return '';

        $image = PlatformComponentRenderer::render('image', [
            'src' => $src,
            'alt' => $alt,
            'class' => 'card-education-logo-img',
        ]);
        if ($image === '') return '';

        $layout = (string) ($logo['bg'] ?? 'light');
        return self::load_education_partial('logo.html', [
            'layout' => htmlspecialchars($layout, ENT_QUOTES, 'UTF-8'),
            'logo_img' => $image,
        ]);
    }

    protected static function render_education_links(array $links): string
    {
        if (empty($links)) return '';

        $items = '';
        foreach ($links as $link) {
            $text = (string) ($link['text'] ?? '');
            if ($text === '') continue;

            $url = self::resolve_education_link_url($link);
            if ($url === '') continue;

            $items .= self::render_education_button($link, $url, true);
        }
        if ($items === '') return '';

        return self::load_education_partial('links.html', ['items' => $items]);
    }

    protected static function render_education_projects(array $projects): string
    {
        if (empty($projects)) return '';

        $items = '';
        foreach ($projects as $project) {
            $text = (string) ($project['text'] ?? '');
            if ($text === '') continue;

            $url = self::resolve_education_link_url($project);
            if ($url !== '') {
                $items .= self::render_education_button($project, $url, false);
            } else {
                $items .= self::load_education_partial('project_plain.html', [
                    'text' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
                ]);
            }
        }
        if ($items === '') return '';

        return self::load_education_partial('projects.html', ['items' => $items]);
    }

    protected static function render_education_button(array $item, string $url, bool $external): string
    {
        return (string) PlatformComponentRenderer::render('button', [
            'class' => 'btn btn-primary-small',
            'text' => (string) ($item['text'] ?? ''),
            'link' => $url,
            'svg' => (string) ($item['svg'] ?? ''),
            'target' => $external ? '_blank' : '',
            'rel' => $external ? 'noopener noreferrer' : '',
        ]);
    }

    protected static function resolve_education_link_url(array $item): string
    {
        $postId = (string) ($item['post_id'] ?? '');
        if ($postId !== '') {
            return PlatformPathService::post_link($postId);
        }

        return (string) ($item['link'] ?? '');
    }

    protected static function education_date_range(array $date): string
    {
        $start = (string) ($date['start'] ?? '');
        $end = (string) ($date['end'] ?? '');

        $startLabel = $start !== '' ? PlatformTextService::format_date($start, 'M Y', 'yyyy-mm-dd') : '';
        $endLabel = $end !== '' && strtolower($end) !== 'present' && strtolower($end) !== 'in progress'
            ? PlatformTextService::format_date($end, 'M Y', 'yyyy-mm-dd')
            : ($end !== '' ? 'Present' : '');

        if ($startLabel !== '' && $endLabel !== '' && $startLabel === $endLabel) {
            return $startLabel;
        }
        $parts = array_values(array_filter([$startLabel, $endLabel], fn ($v) => $v !== ''));
        return implode(' - ', $parts);
    }

    protected static function as_list(mixed $value): array
    {
        if (!is_array($value)) return [];
        return array_values(array_map('strval', $value));
    }
}
