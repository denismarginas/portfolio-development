<?php

/**
 * Grid of resume cards.
 *
 * $data:
 *   id             string  id of the <ul> (default "resumes")
 *   type           string  item file (default "resume" -> data_items_resume.json)
 *   lang           string|array  only these languages (settings.lang), default: all
 *   sort           array   utility_sort rules (default: date.publish desc)
 *   filter_by      object  keep only items matching ALL entries (utility_filter), e.g.
 *                          { "settings.lang": "en", "max_items": 1 } or { "_id": ["cv-3-english", "cv-3-romanian"] }
 *   exclude_by     array   drop items matching ANY entry (utility_filter)
 *   max_items      int     limit (default: all)
 *   date_format    string  PlatformTextService::format_date format (default "M j, Y" -> "Sep 27, 2024")
 *   download_text  string  aria-label/title of the icon-only download button (default "Download")
 *   lang_labels    object  { "en": "English", "ro": "Romanian" } badge text per settings.lang (default: upper-case code)
 *   empty_text     string  text when there are no resumes (default: nothing rendered)
 *   columns        int     fixed number of columns on desktop (default: automatic, cards >= 340px)
 *
 * Item (data_items_resume.json):
 *   settings.render, settings.lang, data.seo.title, data.seo.description, data.date.publish,
 *   data.media.image.img (full image, popup), data.media.image.thumbnail, data.media.pdf
 */
class resume_listing
{
    private const HTML_DIR = __DIR__ . '/../html/';

    public static function render(array $data = []): string
    {
        $items = self::items($data);
        $id = trim((string) ($data['id'] ?? '')) ?: 'resumes';

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= self::item($item, $data);
        }

        if ($itemsHtml === '') {
            $empty = trim((string) ($data['empty_text'] ?? ''));
            return $empty === '' ? '' : '<p class="resume-listing-empty">' . self::e($empty) . '</p>';
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'id' => self::e($id),
            'count' => (string) count($items),
            'columns_attr' => ($cols = (int) ($data['columns'] ?? 0)) > 0 ? ' data-columns="' . min($cols, 6) . '" style="--resume-columns: ' . min($cols, 6) . ';"' : '',
            'items' => $itemsHtml,
        ]);
    }

    private static function items(array $data): array
    {
        $type = trim((string) ($data['type'] ?? '')) ?: 'resume';
        $items = PlatformDataService::get_all_items_from_file($type) ?? [];
        if (!is_array($items)) return [];

        $langs = array_filter(array_map('strtolower', (array) ($data['lang'] ?? [])));
        if (!empty($langs)) {
            $items = array_filter($items, fn ($item): bool =>
                is_array($item) && in_array(strtolower((string) ($item['settings']['lang'] ?? '')), $langs, true)
            );
        }

        $items = PlatformComponentRenderer::value('utility_filter', [
            'items' => $items,
            'filter_by' => $data['filter_by'] ?? [],
            'exclude_by' => $data['exclude_by'] ?? [],
            'sort' => $data['sort'] ?? [['by' => 'date.publish', 'order' => 'desc']],
            'max_items' => $data['max_items'] ?? 0,
        ]);
        return is_array($items) ? $items : [];
    }

    private static function item(array $item, array $data): string
    {
        $d = is_array($item['data'] ?? null) ? $item['data'] : [];
        $title = trim((string) ($d['seo']['title'] ?? ''));
        $description = trim((string) ($d['seo']['description'] ?? ''));
        $publish = trim((string) ($d['date']['publish'] ?? ''));
        $lang = strtolower(trim((string) ($item['settings']['lang'] ?? '')));
        $pdf = self::asset_url((string) ($d['media']['pdf'] ?? ''));
        $img = (string) ($d['media']['image']['img'] ?? '');
        $thumb = (string) ($d['media']['image']['thumbnail'] ?? '') ?: $img;

        if ($title === '' && $thumb === '' && $pdf === '') return '';

        $titleHtml = $pdf !== ''
            ? '<a class="resume-card-title" href="' . self::e($pdf) . '" target="_blank" rel="noopener">' . self::e($title) . '</a>'
            : '<span class="resume-card-title">' . self::e($title) . '</span>';

        $dateHtml = '';
        if ($publish !== '') {
            $format = (string) ($data['date_format'] ?? 'M j, Y');
            $dateHtml = '<time class="resume-card-date" datetime="' . self::e($publish) . '">'
                . self::e(PlatformTextService::format_date($publish, $format)) . '</time>';
        }

        $langHtml = '';
        if ($lang !== '') {
            $labels = is_array($data['lang_labels'] ?? null) ? $data['lang_labels'] : [];
            $langHtml = '<span class="resume-card-lang">' . self::e((string) ($labels[$lang] ?? strtoupper($lang))) . '</span>';
        }

        $downloadHtml = '';
        if ($pdf !== '') {
            $label = self::e((string) ($data['download_text'] ?? 'Download') . ($title !== '' ? ' ' . $title : ''));
            $downloadHtml = '<a class="btn btn-primary-small resume-card-download" href="' . self::e($pdf) . '" download'
                . ' aria-label="' . $label . '" title="' . $label . '">'
                . (string) PlatformComponentRenderer::render('svg', ['icon' => 'download', 'class' => 'resume-card-download-icon'])
                . '</a>';
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/item.html', [
            'item_id' => self::e(trim((string) ($item['_id'] ?? ''))),
            'lang' => self::e($lang),
            'preview' => self::preview($thumb, $img, $title),
            'title_html' => $titleHtml,
            'description_html' => $description !== '' ? '<span class="resume-card-description">' . self::e($description) . '</span>' : '',
            'lang_html' => $langHtml,
            'date_html' => $dateHtml,
            'download_html' => $downloadHtml,
        ]);
    }

    /** Thumbnail + resume icon; click opens the full image in the popup. */
    private static function preview(string $thumb, string $img, string $title): string
    {
        if ($thumb === '') return '';

        $thumbHtml = (string) PlatformComponentRenderer::render('image', [
            'src' => $thumb,
            'alt' => $title !== '' ? $title . ' - preview' : 'Resume preview',
            'class' => 'resume-card-thumbnail',
            'lazy' => true,
        ]);
        if ($thumbHtml === '') return '';

        $thumbHtml .= (string) PlatformComponentRenderer::render('svg', ['icon' => 'resume', 'class' => 'resume-card-icon']);

        $fullHtml = $img !== '' ? (string) PlatformComponentRenderer::render('image', [
            'src' => $img,
            'alt' => $title,
            'class' => 'resume-card-full',
        ]) : '';

        if ($fullHtml === '') {
            return '<div class="resume-card-preview">' . $thumbHtml . '</div>';
        }

        return (string) PlatformComponentRenderer::render('popup', [
            'trigger' => $thumbHtml,
            'content' => $fullHtml,
            'class' => 'resume-card-preview',
        ]);
    }

    /** "src/content/pdf/..." -> URL relative to the current page (works in dev and dist). */
    private static function asset_url(string $path): string
    {
        $path = trim($path);
        if ($path === '') return '';
        if (PlatformUrlService::is_external_url($path)) return $path;
        return rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . ltrim($path, '/');
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
