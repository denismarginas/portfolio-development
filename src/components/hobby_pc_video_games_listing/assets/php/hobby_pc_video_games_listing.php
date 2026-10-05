<?php

require_once __DIR__ . '/parts/hobby_pc_video_games_listing_items.php';

/**
 * Grid of PC video game cards.
 *
 * $data:
 *   id            string  id of the <ul> (hobby_pc_video_games_filters targets it), default "hobby-pc-video-games"
 *   type, items, sort, max_items   see hobby_pc_video_games_listing_items
 *   render_hidden bool    also render items with "display": false (default true). They get
 *                         data-display="false" + hidden, and are shown by the filters' Display: All.
 *   lazy          bool    lazy-load the banners (default true)
 *   empty_text    string  text when no games are shown (default "No games found.")
 */
class hobby_pc_video_games_listing
{
    private const HTML_DIR = __DIR__ . '/../html/';

    public static function render(array $data = []): string
    {
        $id = trim((string) ($data['id'] ?? '')) ?: 'hobby-pc-video-games';
        $renderHidden = ($data['render_hidden'] ?? $data['show_hidden'] ?? true) !== false;
        $items = hobby_pc_video_games_listing_items::find(array_merge($data, ['show_hidden' => $renderHidden]));
        $lazy = ($data['lazy'] ?? true) !== false;

        $icons = [
            'rank' => self::icon('star'),
            'playtime' => self::icon('clock'),
            'tags' => self::icon('tag-plus'),
        ];

        $itemsHtml = '';
        $visible = 0;
        foreach ($items as $item) {
            $itemsHtml .= self::item($item, $icons, $lazy);
            if (!hobby_pc_video_games_listing_items::is_hidden($item)) $visible++;
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'id' => self::e($id),
            'type' => self::e(hobby_pc_video_games_listing_items::type($data)),
            'count' => (string) $visible,
            'items' => $itemsHtml,
            'empty_text' => self::e((string) ($data['empty_text'] ?? 'No games found.')),
            'empty_hidden' => $visible > 0 ? ' hidden' : '',
        ]);
    }

    private static function item(array $item, array $icons, bool $lazy): string
    {
        $name = trim((string) ($item['name'] ?? ''));
        $rank = hobby_pc_video_games_listing_items::number($item['rank'] ?? null);
        $playtime = hobby_pc_video_games_listing_items::number($item['playtime'] ?? null);
        $tags = hobby_pc_video_games_listing_items::tags($item);

        $banner = '';
        $src = trim((string) ($item['banner'] ?? ''));
        if ($src !== '') {
            $banner = '<img class="hobby-pc-video-game-banner" src="' . self::e($src) . '" alt="' . self::e($name) . '" width="100" height="150"'
                . ($lazy ? ' loading="lazy" decoding="async"' : '') . '>';
        }

        $rankHtml = '';
        if ($rank !== null) {
            $rankText = self::number_text($rank);
            $rankHtml = '<div class="hobby-pc-video-game-rank" data-rank="' . self::e((string) round($rank)) . '" title="Rank">'
                . $icons['rank'] . '<span>' . self::e($rankText) . '</span></div>';
        }

        $playtimeHtml = '';
        $playtimeRaw = trim((string) ($item['playtime'] ?? ''));
        if ($playtimeRaw !== '') {
            $playtimeHtml = '<div class="hobby-pc-video-game-playtime" title="Playtime">'
                . $icons['playtime'] . '<span>' . self::e($playtimeRaw) . '</span></div>';
        }

        $tagsHtml = '';
        if (!empty($tags)) {
            $tagsHtml = '<details class="hobby-pc-video-game-tags"><summary aria-label="Tags">' . $icons['tags'] . '</summary><ul>';
            foreach ($tags as $tag) {
                $tagsHtml .= '<li>' . self::e($tag) . '</li>';
            }
            $tagsHtml .= '</ul></details>';
        }

        $hidden = hobby_pc_video_games_listing_items::is_hidden($item);

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/item.html', [
            'display' => $hidden ? 'false' : 'true',
            'hidden_attr' => $hidden ? ' hidden' : '',
            'item_id' => self::e(hobby_pc_video_games_listing_items::id($item)),
            'name' => self::e($name),
            'rank' => $rank !== null ? self::e(self::number_text($rank)) : '',
            'playtime' => $playtime !== null ? self::e(self::number_text($playtime)) : '',
            'banner' => $banner,
            'rank_html' => $rankHtml,
            'playtime_html' => $playtimeHtml,
            'tags_html' => $tagsHtml,
        ]);
    }

    private static function number_text(float $value): string
    {
        return floor($value) === $value ? (string) (int) $value : (string) $value;
    }

    private static function icon(string $name): string
    {
        return (string) PlatformComponentRenderer::render('svg', ['icon' => $name, 'class' => 'hobby-pc-video-game-icon']);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
