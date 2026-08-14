<?php

class Cv
{
    public static function render(array $data = []): string
    {
        $items = $data['items'] ?? [];

        if (empty($items)) {
            $cvType = PostTypeRegistry::get('cv');
            if ($cvType) {
                $items = DataService::getPosts('cv');
            }
        }

        if (empty($items)) {
            return '';
        }

        $versionFilter = $data['version_filter'] ?? null;
        if ($versionFilter !== null) {
            $items = array_filter($items, function ($item) use ($versionFilter) {
                return ($item['version'] ?? null) == $versionFilter;
            });
        }

        $html = '<div class="dm-cv-list">';
        foreach ($items as $item) {
            $html .= self::render_card($item);
        }
        $html .= '</div>';

        return $html;
    }

    protected static function render_card(array $item): string
    {
        $name = $item['name'] ?? $item['title'] ?? 'CV';
        $thumbnail = $item['thumbnail'] ?? '';
        $image = $item['image'] ?? '';
        $pdf = $item['pdf'] ?? '';
        $description = $item['description'] ?? '';

        $html = '<div class="dm-cv-card" data-motion="transition-fade-0" data-duration="0.5s">';

        if (!empty($image)) {
            $html .= ComponentRenderer::render_component('image', [
                'src' => $image,
                'class' => 'dm-cv-preview',
                'popup' => true,
            ]);
        }

        $html .= '<div class="dm-cv-info">';
        $html .= '<h3 class="dm-cv-title">' . htmlspecialchars($name) . '</h3>';
        if (!empty($description)) {
            $html .= '<p class="dm-cv-description">' . htmlspecialchars($description) . '</p>';
        }
        if (!empty($pdf)) {
            $html .= '<a class="dm-cv-download" href="' . htmlspecialchars($pdf) . '" target="_blank" data-button="primary">Download PDF</a>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
