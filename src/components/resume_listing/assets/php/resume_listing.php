<?php

/**
 * Grid of resume cards.
 *
 * $data:
 *   id             string  id of the <ul> (default "resumes")
 *   type           string  item file (default "resume" -> data_items_resume.json)
 *   lang           string|array  only these languages (data.lang), default: all
 *   sort           array   utility_sort rules (default: date.publish desc)
 *   filter_by      object  keep only items matching ALL entries (utility_filter), e.g.
 *                          { "lang": "en", "max_items": 1 }, { "model": 3 } or { "_id": ["cv-3-english", "cv-3-romanian"] }
 *   exclude_by     array   drop items matching ANY entry (utility_filter)
 *   max_items      int     limit (default: all)
 *   date_format    string  PlatformTextService::format_date format (default "M j, Y" -> "Sep 27, 2024")
 *   download_text  string  aria-label/title of the icon-only download button (default "Download")
 *   preview_popup  bool    View button + thumbnail open the big view (resume_view) in the popup (default true);
 *                          the URL hash #<item _id> (e.g. #cv-3-english) opens it on load (popup data-popup-hash);
 *                          false: no View button, the thumbnail opens only the full image
 *   view_text      string  aria-label/title of the icon-only view button (default "View")
 *   open_text      string  "Open PDF" button text in the view popup (default "Open PDF")
 *   download_label string  download button text in the view popup (default "Download PDF")
 *   lang_labels    object  { "en": "English", "ro": "Romanian" } badge text per data.lang (default: upper-case code)
 *   empty_text     string  text when there are no resumes (default: nothing rendered)
 *   columns        int     fixed number of columns on desktop (default: automatic, cards >= 340px)
 *
 * Item (data_items_resume.json):
 *   { "_id", "settings": { "render" },
 *     "data": { "model": 3, "lang": "en", "seo": { "title", "description" }, "date": { "publish" },
 *               "media": { "image" (full image, popup), "thumbnail", "pdf" } } }
 *   The old keys (settings.lang, media.image.img, media.image.thumbnail) still work.
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
                is_array($item) && in_array(self::lang($item), $langs, true)
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
        $lang = self::lang($item);
        $media = is_array($d['media'] ?? null) ? $d['media'] : [];
        $image = $media['image'] ?? '';
        $pdf = self::asset_url((string) ($media['pdf'] ?? ''));
        $img = is_array($image) ? (string) ($image['img'] ?? '') : (string) $image;
        $thumb = (string) ($media['thumbnail'] ?? (is_array($image) ? ($image['thumbnail'] ?? '') : '')) ?: $img;

        if ($title === '' && $thumb === '' && $pdf === '') return '';

        $titleHtml = $pdf !== ''
            ? '<a class="resume-card-title" href="' . self::e($pdf) . '" target="_blank" rel="noopener">' . self::e($title) . '</a>'
            : '<span class="resume-card-title">' . self::e($title) . '</span>';

        $dateHtml = '';
        $dateText = '';
        if ($publish !== '') {
            $format = (string) ($data['date_format'] ?? 'M j, Y');
            $dateText = (string) PlatformTextService::format_date($publish, $format);
            $dateHtml = '<time class="resume-card-date" datetime="' . self::e($publish) . '">' . self::e($dateText) . '</time>';
        }

        $langHtml = '';
        $langLabel = '';
        if ($lang !== '') {
            $labels = is_array($data['lang_labels'] ?? null) ? $data['lang_labels'] : [];
            $langLabel = (string) ($labels[$lang] ?? strtoupper($lang));
            $langHtml = '<span class="resume-card-lang">' . self::e($langLabel) . '</span>';
        }

        $downloadHtml = '';
        if ($pdf !== '') {
            $label = self::e((string) ($data['download_text'] ?? 'Download') . ($title !== '' ? ' ' . $title : ''));
            $downloadHtml = '<a class="btn btn-primary-small resume-card-download" href="' . self::e($pdf) . '" download'
                . ' aria-label="' . $label . '" title="' . $label . '">'
                . (string) PlatformComponentRenderer::render('svg', ['icon' => 'download', 'class' => 'resume-card-download-icon'])
                . '</a>';
        }

        // big version of the card (resume_view), shown in the popup (View button + thumbnail click)
        $viewHtml = '';
        if (($data['preview_popup'] ?? true) !== false) {
            $viewHtml = (string) PlatformComponentRenderer::render('resume_view', [
                'image' => $img !== '' ? $img : $thumb,
                'title' => $title,
                'description' => $description,
                'lang' => $langLabel,
                'date' => $publish,
                'date_text' => $dateText,
                'pdf' => $pdf,
                'download_label' => $data['download_label'] ?? 'Download PDF',
                'open_text' => $data['open_text'] ?? 'Open PDF',
            ]);
        }

        $viewButtonHtml = '';
        if ($viewHtml !== '') {
            $viewLabel = self::e((string) ($data['view_text'] ?? 'View') . ($title !== '' ? ' ' . $title : ''));
            $viewButtonHtml = (string) PlatformComponentRenderer::render('popup', [
                'trigger' => '<button type="button" class="btn btn-primary-small resume-card-view" aria-label="' . $viewLabel . '" title="' . $viewLabel . '">'
                    . (string) PlatformComponentRenderer::render('svg', ['icon' => 'eye', 'class' => 'resume-card-view-icon'])
                    . '</button>',
                'content' => $viewHtml,
                'class' => 'resume-view-trigger',
                'hash' => trim((string) ($item['_id'] ?? '')),   // #cv-3-english in the URL opens this view
            ]);
        }

        $actionsHtml = ($viewButtonHtml . $downloadHtml) !== ''
            ? '<div class="resume-card-actions">' . $viewButtonHtml . $downloadHtml . '</div>'
            : '';

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/item.html', [
            'item_id' => self::e(trim((string) ($item['_id'] ?? ''))),
            'id_attr' => ($itemId = trim((string) ($item['_id'] ?? ''))) !== '' ? ' id="' . self::e($itemId) . '"' : '',
            'model' => self::e(trim((string) ($d['model'] ?? ''))),
            'lang' => self::e($lang),
            'preview' => self::preview($thumb, $img, $title, $viewHtml),
            'title_html' => $titleHtml,
            'description_html' => $description !== '' ? '<span class="resume-card-description">' . self::e($description) . '</span>' : '',
            'lang_html' => $langHtml,
            'date_html' => $dateHtml,
            'actions_html' => $actionsHtml,
        ]);
    }

    /** data.lang (old: settings.lang), lower case. */
    private static function lang(array $item): string
    {
        return strtolower(trim((string) ($item['data']['lang'] ?? $item['settings']['lang'] ?? '')));
    }

    /** Thumbnail + resume icon; click opens the big view (same as the View button), or only the full image when preview_popup is false. */
    private static function preview(string $thumb, string $img, string $title, string $viewHtml): string
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

        $content = $viewHtml;
        if ($content === '' && $img !== '') {
            $content = (string) PlatformComponentRenderer::render('image', [
                'src' => $img,
                'alt' => $title,
                'class' => 'resume-card-full',
            ]);
        }

        if ($content === '') {
            return '<div class="resume-card-preview">' . $thumbHtml . '</div>';
        }

        return (string) PlatformComponentRenderer::render('popup', [
            'trigger' => $thumbHtml,
            'content' => $content,
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
