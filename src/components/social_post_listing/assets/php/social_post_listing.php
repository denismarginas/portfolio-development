<?php

/**
 * Feed of social posts, newest first.
 *
 *   {
 *     "component": "social_post_listing",
 *     "data": {
 *       "author": { "name": "Denis Marginas", "avatar": "src/content/img/projects/denismarginas/media/logo/logo3.webp" }
 *     }
 *   }
 *
 * data:
 *   type             string  item file (default "social_post" -> data_items_social_post.json)
 *   author           object  { "name", "avatar" } shown on every post
 *   sort             array   utility_sort rules (default: date.publish desc)
 *   filter_by        object  utility_filter (e.g. { "_id": ["..."] })
 *   exclude_by       array   utility_filter
 *   max_items        int     limit (default: all)
 *   date_format      string  PlatformTextService::format_date format (default "j F, Y" -> "6 December, 2022")
 *   description_max  int     longer texts get Show More / Show Less (text_collapse, default 300)
 *   index            bool    true = narrow sticky anchor_index of the posts next to the feed (date + first
 *                            line), links jump to the post, the post in view is marked (default false;
 *                            hidden below 768px)
 *   index_title      string  heading of that list (default "Posts")
 *   class, id        string  optional
 *
 * Item (data_items_social_post.json):
 *   settings.render         false = hidden
 *   data.date.publish       "YYYY-MM-DD"
 *   data.description        plain text; line breaks kept, urls become links
 *   data.media.images       ["src/...", ...]  1-4 images in a grid (popup gallery)
 *   data.media.video        _id of a video in data_items_video.json (player + thumbnail from there)
 *   data.buttons_title      optional text before the buttons (e.g. "Links:")
 *   data.buttons            [{ "text", "link", "svg" }]  link: url, "src/..." file or { "_id": "page-or-item" }
 */
class social_post_listing
{
    public static function render(array $data = []): string
    {
        $type = self::str($data['type'] ?? '') ?: 'social_post';
        $items = PlatformDataService::get_all_items_from_file($type) ?? [];
        if (!is_array($items)) return '';
        $items = array_filter($items, fn ($item): bool => is_array($item) && ($item['settings']['render'] ?? true) !== false);

        $items = PlatformComponentRenderer::value('utility_filter', [
            'items' => array_values($items),
            'filter_by' => $data['filter_by'] ?? [],
            'exclude_by' => $data['exclude_by'] ?? [],
            'sort' => $data['sort'] ?? [['by' => 'date.publish', 'order' => 'desc']],
            'max_items' => $data['max_items'] ?? 0,
        ]);
        if (!is_array($items) || $items === []) return '';

        $author = is_array($data['author'] ?? null) ? $data['author'] : [];
        $authorName = self::str($author['name'] ?? '');
        $avatarSrc = self::str($author['avatar'] ?? '');
        $avatar = $avatarSrc !== '' ? (string) PlatformComponentRenderer::render('image', ['src' => $avatarSrc, 'alt' => $authorName, 'class' => 'social-post-avatar-img', 'lazy' => true]) : '';
        $videos = self::videos();

        $html = '';
        $links = [];
        $dateFormat = self::str($data['date_format'] ?? '') ?: 'j F, Y';
        foreach ($items as $item) {
            $post = self::post($item, $data, $authorName, $avatar, $videos);
            if ($post === '') continue;
            $html .= $post;
            if (($link = self::index_item($item)) !== null) $links[] = $link;
        }
        if ($html === '') return '';

        $class = self::str($data['class'] ?? '');
        $id = self::str($data['id'] ?? '');

        $index = ($data['index'] ?? false) === true && $links !== []
            ? (string) PlatformComponentRenderer::render('anchor_index', [
                'title' => self::str($data['index_title'] ?? '') ?: 'Posts',
                'items' => $links,
                'date_format' => $dateFormat,
                'sticky' => true,
                'class' => 'social-post-index',
            ])
            : '';

        return PlatformTemplateRenderer::render([
            'extra_class' => ($index !== '' ? ' social-post-listing-indexed' : '') . ($class !== '' ? ' ' . self::e($class) : ''),
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'items' => $html,
            'index' => $index,
        ]);
    }

    /** anchor_index item: post id, first line of the text, publish date. */
    private static function index_item(array $item): ?array
    {
        $id = self::str($item['_id'] ?? '');
        if ($id === '') return null;
        $d = is_array($item['data'] ?? null) ? $item['data'] : [];
        $line = trim(strtok(self::str($d['description'] ?? '') ?: ' ', "\n"));
        if (mb_strlen($line) > 60) $line = rtrim(mb_substr($line, 0, 57)) . '...';

        return ['id' => $id, 'text' => $line !== '' ? $line : $id, 'date' => self::str($d['date']['publish'] ?? '')];
    }

    private static function post(array $item, array $data, string $authorName, string $avatar, array $videos): string
    {
        $d = is_array($item['data'] ?? null) ? $item['data'] : [];
        $id = self::str($item['_id'] ?? '');

        $publish = self::str($d['date']['publish'] ?? '');
        $date = $publish !== '' ? PlatformTextService::format_date($publish, self::str($data['date_format'] ?? '') ?: 'j F, Y') : '';

        $header = '<div class="social-post-header">'
            . ($avatar !== '' ? '<div class="social-post-avatar">' . $avatar . '</div>' : '')
            . '<div class="social-post-meta">'
            . ($authorName !== '' ? '<span class="social-post-author">' . self::e($authorName) . '</span>' : '')
            . ($date !== '' ? '<time class="social-post-date" datetime="' . self::e($publish) . '">' . self::e($date) . '</time>' : '')
            . '</div></div>';

        $text = self::str($d['description'] ?? '');
        $body = $text !== '' ? (string) PlatformComponentRenderer::render('text_collapse', [
            'text' => self::text_html($text),
            'max_chars' => (int) ($data['description_max'] ?? 300),
            'class' => 'social-post-text',
        ]) : '';

        $media = is_array($d['media'] ?? null) ? $d['media'] : [];
        $mediaHtml = self::images((array) ($media['images'] ?? []), 'social-post-' . ($id !== '' ? $id : md5($text)))
            . self::video(self::str($media['video'] ?? ''), $videos);

        return '<article class="social-post card-outline"' . ($id !== '' ? ' id="' . self::e($id) . '"' : '') . '>'
            . $header . $body
            . ($mediaHtml !== '' ? '<div class="social-post-media">' . $mediaHtml . '</div>' : '')
            . self::buttons($d)
            . '</article>';
    }

    /** Plain text -> escaped html with <br> line breaks and clickable urls. */
    private static function text_html(string $text): string
    {
        $html = self::e($text);
        $html = preg_replace_callback('~\b(?:https?://|www\.)[^\s<]+[^\s<.,;:!?)"\']~i', static function (array $m): string {
            $href = stripos($m[0], 'www.') === 0 ? 'https://' . $m[0] : $m[0];
            return '<a href="' . $href . '" target="_blank" rel="noopener">' . $m[0] . '</a>';
        }, $html);
        return str_replace(["\r\n", "\n"], '<br>', $html);
    }

    /** 1-4 images in a grid; every image opens the popup gallery of its post. */
    private static function images(array $images, string $group): string
    {
        $cells = '';
        $count = 0;
        foreach ($images as $src) {
            if (!is_string($src) || ($src = trim($src)) === '') continue;
            $img = (string) PlatformComponentRenderer::render('image', ['src' => $src, 'alt' => '', 'class' => 'social-post-img', 'lazy' => true]);
            if (trim($img) === '') continue;
            $cells .= '<div class="social-post-gallery-item" data-popup="true" data-popup-group="' . self::e($group) . '">' . $img . '</div>';
            $count++;
        }
        return $count > 0 ? '<div class="social-post-gallery" data-count="' . min($count, 4) . '">' . $cells . '</div>' : '';
    }

    /** Video item (data_items_video.json) by _id -> video player with its thumbnail. */
    private static function video(string $videoId, array $videos): string
    {
        $video = $videoId !== '' ? ($videos[$videoId] ?? null) : null;
        if (!is_array($video)) return '';
        $media = $video['data']['media']['video'] ?? [];
        $src = self::str($media['video'] ?? '');
        if ($src === '') return '';

        return (string) PlatformComponentRenderer::render('video', [
            'src' => $src,
            'thumbnail' => self::thumbnail(self::str($media['video_thumbnail'] ?? '')),
            'appearance' => ['colors' => ['primary' => self::str($video['settings']['appearance']['colors']['primary'] ?? '') ?: '#fff']],
        ]);
    }

    private static function buttons(array $d): string
    {
        $html = '';
        foreach ((array) ($d['buttons'] ?? []) as $button) {
            if (!is_array($button)) continue;
            $link = $button['link'] ?? '';
            $external = is_string($link);
            if (is_string($link)) $link = self::asset_url(trim($link));
            $html .= (string) PlatformComponentRenderer::render('button', [
                'text' => self::str($button['text'] ?? ''),
                'link' => $link,
                'svg' => self::str($button['svg'] ?? ''),
                'class' => 'btn btn-primary-small',
                'target' => $external ? '_blank' : '',
                'rel' => $external ? 'noopener' : '',
            ]);
        }
        if ($html === '') return '';

        $title = self::str($d['buttons_title'] ?? '');
        return '<div class="social-post-actions">'
            . ($title !== '' ? '<span class="social-post-actions-title">' . self::e($title) . '</span>' : '')
            . $html . '</div>';
    }

    /** data_items_video.json by _id. */
    private static function videos(): array
    {
        $out = [];
        foreach ((array) (PlatformDataService::get_all_items_from_file('video') ?? []) as $video) {
            if (is_array($video) && ($id = self::str($video['_id'] ?? '')) !== '') $out[$id] = $video;
        }
        return $out;
    }

    /** Thumbnail (relative to src/content/img/, "src/..." or url) -> url for background-image; '' when missing. */
    private static function thumbnail(string $path): string
    {
        $path = trim(str_replace('\\', '/', $path));
        if ($path === '' || PlatformUrlService::is_external_url($path)) return $path;
        $path = str_starts_with(ltrim($path, '/'), 'src/') ? ltrim($path, '/') : 'src/content/img/' . ltrim($path, '/');
        $root = defined('ENGINE_PROJECT_ROOT') ? rtrim(ENGINE_PROJECT_ROOT, '/\\') . '/' : '';
        return is_file($root . $path) ? rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . $path : '';
    }

    /** "src/content/..." -> relative to the current page; urls kept. */
    private static function asset_url(string $path): string
    {
        if ($path === '' || PlatformUrlService::is_external_url($path) || preg_match('#^(mailto:|tel:|\#)#i', $path)) return $path;
        return rtrim(PlatformPathService::asset_relative_prefix(), '/') . '/' . ltrim($path, '/');
    }

    private static function str(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
