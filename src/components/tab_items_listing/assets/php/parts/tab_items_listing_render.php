<?php

trait tab_items_listing_render
{
    public static function render(array $data = []): string
    {
        $type = (string) ($data['type'] ?? $data['item_type'] ?? '');
        if ($type === '' && !empty($data['items'])) {
            $type = (string) ($data['type'] ?? $data['item_type'] ?? '');
        }

        $items = $data['items'] ?? PlatformDataService::get_all_items_from_file($type);
        if (!is_array($items) || empty($items)) return '';

        $labels = '';
        $itemsHtml = '';
        foreach ($items as $item) {
            if (($item['settings']['render'] ?? true) === false) continue;

            $itemId = (string) ($item['_id'] ?? $item['item_id'] ?? $item['post_id'] ?? '');
            if ($itemId === '') continue;

            $labels .= self::render_label($itemId, $item, $data);
            $itemsHtml .= self::render_content($item, $data, $itemId);
        }

        if ($itemsHtml === '') return '';

        $layout = (string) ($data['layout'] ?? '');
        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template.html', [
            'labels' => $labels,
            'items' => $itemsHtml,
            'layout_attr' => $layout !== '' ? ' layout="' . htmlspecialchars($layout, ENT_QUOTES, 'UTF-8') . '"' : '',
            'type' => htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
        ]);
    }

    protected static function render_label(string $itemId, array $item, array $data): string
    {
        $itemData = is_array($item['data'] ?? null) ? $item['data'] : $item;
        $education = $itemData['education'] ?? [];
        $media = $itemData['media'] ?? [];
        $logo = $media['logo'] ?? [];

        $labelText = (string) ($education['type'] ?? '');
        if ($labelText === '') {
            $labelText = self::item_title($item);
        }

        $badgeImg = '';
        $src = (string) ($logo['img_min'] ?? $logo['img'] ?? '');
        if ($src !== '') {
            $badgeImg = PlatformComponentRenderer::render('image', [
                'src' => $src,
                'alt' => $labelText,
                'class' => 'dm-tab-items-listing__badge-img',
            ]);
        }

        $badgeLayout = (string) ($logo['bg'] ?? 'light');

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/parts/label.html', [
            'item_id' => htmlspecialchars($itemId, ENT_QUOTES, 'UTF-8'),
            'badge_img' => $badgeImg,
            'badge_layout' => htmlspecialchars($badgeLayout, ENT_QUOTES, 'UTF-8'),
            'label_text' => htmlspecialchars($labelText, ENT_QUOTES, 'UTF-8'),
        ]);
    }

    protected static function render_content(array $item, array $data, string $itemId = ''): string
    {
        $type = (string) ($data['type'] ?? $data['item_type'] ?? '');
        $method = 'render_content_' . str_replace('-', '_', $type);
        if (method_exists(self::class, $method)) {
            return self::$method($item, $data, $itemId);
        }

        $contentTemplate = $data['content'] ?? $data['tab_item_content_template'] ?? null;
        if (is_array($contentTemplate)) {
            $html = self::render_component_spec($contentTemplate, $item);
            if ($html !== '') return $html;
        }

        return '';
    }

    protected static function render_content_education(array $item, array $data, string $itemId = ''): string
    {
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

        $dateIconHtml = $dateLabel !== '' ? PlatformComponentRenderer::render('svg', [
            'icon' => 'time',
            'class' => 'tab-items-listing-date-icon',
        ]) : '';
        $locationIconHtml = $location !== '' ? PlatformComponentRenderer::render('svg', [
            'icon' => 'location',
            'class' => 'tab-items-listing-location-icon',
        ]) : '';

        $metaHtml = self::load_education_partial('meta.html', [
            'type_tag' => $typeTagHtml,
            'date_icon' => $dateIconHtml,
            'date' => htmlspecialchars($dateLabel, ENT_QUOTES, 'UTF-8'),
            'location_icon' => $locationIconHtml,
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
                    'class' => 'tab-items-listing-discipline-icon',
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

        $display = (string) ($data['display'] ?? 'detailed');

        return self::load_education_partial('template.html', [
            'item_id' => htmlspecialchars($itemId, ENT_QUOTES, 'UTF-8'),
            'display' => htmlspecialchars($display, ENT_QUOTES, 'UTF-8'),
            'logo_html' => $logoHtml,
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'meta_html' => $metaHtml,
            'profession_html' => $professionHtml,
            'disciplines_html' => $disciplinesHtml,
            'links_container_html' => $linksContainerHtml,
        ]);
    }

    protected static function render_component_spec(array $spec, array $item): string
    {
        $component = (string) ($spec['component'] ?? 'card');
        $rawParams = $spec['params'] ?? $spec['data'] ?? [];
        if (!is_array($rawParams)) {
            $rawParams = [];
        }

        $context = [
            'post_id' => (string) ($item['item_id'] ?? $item['post_id'] ?? $item['_id'] ?? ''),
            'data' => $item['data'] ?? [],
            'settings' => $item['settings'] ?? [],
            'img_base' => '',
        ];

        $params = self::resolve_template_value($rawParams, $context);
        if (!is_array($params)) {
            $params = [];
        }
        if (isset($rawParams['title']) && is_string($rawParams['title'])) {
            $params['_title_raw'] = $rawParams['title'];
        }
        $params['post_current_data'] = $item;
        $params['item'] = $item;

        return (string) PlatformComponentRenderer::render($component, $params);
    }

    protected static function resolve_template_value(mixed $value, array $context): mixed
    {
        if (is_array($value)) {
            if (isset($value['component']) && is_string($value['component'])) {
                return $value;
            }
            $out = [];
            foreach ($value as $key => $item) {
                $out[$key] = self::resolve_template_value($item, $context);
            }
            return $out;
        }
        if (!is_string($value)) return $value;

        $trimmed = trim($value);
        if ($trimmed === '') return '';
        if (str_contains($trimmed, '+')) {
            return self::concat_value($trimmed, $context);
        }

        return self::resolve_token($trimmed, $context);
    }

    protected static function concat_value(string $value, array $context): ?string
    {
        $parts = preg_split('/\s*\+(?=\s*|$)/', $value, -1, PREG_SPLIT_NO_EMPTY);
        $out = '';
        foreach ($parts as $part) {
            $resolved = self::resolve_token(trim($part), $context);
            if (is_array($resolved)) {
                $resolved = '';
            }
            $out .= (string) $resolved;
        }
        return $out;
    }

    protected static function resolve_token(string $token, array $context): mixed
    {
        $len = strlen($token);
        if ($token !== '' && ($token[0] === '"' && $token[$len - 1] === '"' || $token[0] === "'" && $token[$len - 1] === "'") && $len >= 2) {
            return substr($token, 1, -1);
        }
        if (str_starts_with($token, '@')) {
            $ref = substr($token, 1);

            if (!str_contains($ref, '/')) {
                return self::resolve_ref($ref, $context);
            }

            $segments = explode('/', $ref);
            $out = [];
            foreach ($segments as $segment) {
                $segment = ltrim(trim($segment), '@');
                $resolved = self::resolve_ref($segment, $context);
                $out[] = is_array($resolved) ? (string) ($resolved[0] ?? '') : (string) $resolved;
            }
            return implode('/', $out);
        }
        return $token;
    }

    protected static function resolve_ref(string $ref, array $context): mixed
    {
        if ($ref === 'post_id' || $ref === '_id') {
            return (string) ($context['post_id'] ?? '');
        }
        if ($ref === 'post_link') {
            return PlatformPathService::post_link((string) ($context['post_id'] ?? ''));
        }
        if ($ref === 'img_base') {
            return (string) ($context['img_base'] ?? '');
        }
        if (str_starts_with($ref, 'data.')) {
            return self::mixed_at($context['data'] ?? [], explode('.', substr($ref, 5)));
        }
        if (str_starts_with($ref, 'settings.')) {
            return self::mixed_at($context['settings'] ?? [], explode('.', substr($ref, 9)));
        }
        return $ref;
    }

    protected static function mixed_at(array $data, array $segments): mixed
    {
        $value = $data;
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }
        return $value;
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
            'class' => 'tab-items-listing-logo-img',
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
            $url = self::resolve_education_link_url($link);
            if ($url === '') continue;

            $text = self::synthesize_url_text($url);
            if ($text === '') continue;

            $items .= self::render_education_button($link, $url, true, $text);
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

    protected static function render_education_button(array $item, string $url, bool $external, string $text = ''): string
    {
        if ($text === '') {
            $text = (string) ($item['text'] ?? '');
        }
        if ($text === '') return '';

        return (string) PlatformComponentRenderer::render('button', [
            'class' => 'btn btn-primary-small',
            'text' => $text,
            'link' => $url,
            'svg' => (string) ($item['svg'] ?? ''),
            'target' => $external ? '_blank' : '',
            'rel' => $external ? 'noopener noreferrer' : '',
        ]);
    }

    protected static function synthesize_url_text(string $url): string
    {
        return (string) PlatformComponentRenderer::value('utility_string_urlto', [
            'input' => $url,
            'action' => 'synthesize',
        ]);
    }

    protected static function resolve_education_link_url(array $item): string
    {
        $postId = (string) ($item['post_id'] ?? $item['_id'] ?? '');
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

    protected static function item_title(array $item): string
    {
        $title = $item['data']['seo']['title'] ?? '';
        if (is_string($title) && $title !== '') {
            return $title;
        }
        return (string) ($item['_id'] ?? $item['item_id'] ?? $item['post_id'] ?? '');
    }

    protected static function as_list(mixed $value): array
    {
        if (!is_array($value)) return [];
        return array_values(array_map('strval', $value));
    }
}
