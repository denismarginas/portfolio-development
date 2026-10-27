<?php

/**
 * Migrates project posts (data_post_project.json) from the old aside layout to project_page.
 *
 *   php dev/migrations/migrate_project_page.php --dry-run                 show what would change, write nothing
 *   php dev/migrations/migrate_project_page.php --only=agramix-online-shop,agromir-media
 *   php dev/migrations/migrate_project_page.php                           every project still on section_layout_aside
 *
 * Before:  content = [ section_layout_aside { layout_content [ project_feature_hero, project_visual_web_gallery |
 *                      project_visual_media_gallery (+ text), block_highlight..., block_highlight "Related" (post-items) ],
 *                      layout_aside [ post_short_info, anchor_index ] } ]
 * After:   content = [ project_page {
 *                        data: { overview: { text }, videos: false (when show_video was false) },
 *                        children: [ block_highlight... (case studies), related_projects { title, filter_by, sort, exclude_current } ]
 *                      } ]
 *
 * Dropped (now automatic): project_feature_hero, the galleries (screens / brand / gallery / videos come from the media
 * folders), post_short_info (hero + overview facts), anchor_index (section_nav). Unknown components are kept as children.
 * Posts already on project_page are skipped. Only the migrated posts' "content" changes in the file (same 2-space,
 * Prettier-like formatting); everything else stays byte for byte. Make a backup first.
 */

$root = dirname(__DIR__, 2);
$file = $root . '/src/content/json/data/en/post/data_post_project.json';

$args = array_slice($argv, 1);
$dryRun = in_array('--dry-run', $args, true);
$only = [];
foreach ($args as $arg) {
    if (str_starts_with($arg, '--only=')) $only = array_filter(array_map('trim', explode(',', substr($arg, 7))));
}

$raw = file_get_contents($file);
if ($raw === false) exit("Cannot read $file\n");
if (strncmp($raw, "\xEF\xBB\xBF", 3) === 0) $raw = substr($raw, 3);
// objects stay objects (an empty {} must not become [])
$posts = json_decode($raw, false);
if (!is_array($posts)) exit("Invalid JSON in $file\n");

$changed = 0;
$text = $raw;
foreach ($posts as $post) {
    $id = (string) ($post->_id ?? '');
    if ($only && !in_array($id, $only, true)) continue;

    $content = $post->content ?? null;
    if (!is_array($content) || count($content) !== 1 || ($content[0]->component ?? '') !== 'section_layout_aside') {
        echo "skip  $id (not the old layout)\n";
        continue;
    }

    // only this post's "content" value changes in the file text; everything else stays byte for byte
    $span = content_span($text, $id);
    if ($span === null) {
        echo "skip  $id (\"content\" not found in the file text)\n";
        continue;
    }
    [$from, $to, $indent] = $span;
    $text = substr($text, 0, $from) . encode([migrate($content[0])], $indent) . substr($text, $to);
    $changed++;
    echo "done  $id\n";
}

if ($dryRun) {
    echo "\n$changed post(s) would be migrated (dry run, nothing written).\n";
    exit(0);
}
if ($changed === 0) exit("Nothing to migrate.\n");
if (!is_array(json_decode($text, true))) exit("Result is not valid JSON - nothing written.\n");

file_put_contents($file, $text, LOCK_EX);
echo "\n$changed post(s) migrated -> $file\n";

/**
 * [start, end, indent] of the value of the "content" key of the post $id in the file text
 * (start = its "[", end = after its "]"), null when not found.
 */
function content_span(string $text, string $id): ?array
{
    $idPos = strpos($text, '"_id": ' . json_encode($id, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    if ($idPos === false) return null;
    $lineStart = strrpos(substr($text, 0, $idPos), "\n");
    $indent = $idPos - ($lineStart === false ? 0 : $lineStart + 1);

    // the post's own "content" key: same indentation as its "_id", before the next post
    if (!preg_match('/\n' . str_repeat(' ', $indent) . '"content":\s*\[/', $text, $m, PREG_OFFSET_CAPTURE, $idPos)) return null;
    $next = strpos($text, "\n" . str_repeat(' ', $indent) . '"_id": ', $idPos + 1);
    $open = $m[0][1] + strlen($m[0][0]) - 1;
    if ($next !== false && $open > $next) return null;

    // matching "]" (skipping strings)
    $depth = 0;
    $inString = false;
    for ($i = $open, $n = strlen($text); $i < $n; $i++) {
        $c = $text[$i];
        if ($inString) {
            if ($c === '\\') { $i++; continue; }
            if ($c === '"') $inString = false;
            continue;
        }
        if ($c === '"') { $inString = true; continue; }
        if ($c === '[' || $c === '{') $depth++;
        if ($c === ']' || $c === '}') {
            $depth--;
            if ($depth === 0) return [$open, $i + 1, $indent];
        }
    }
    return null;
}

/**
 * JSON in the file's style (2 spaces, Prettier-like): objects expanded, arrays of plain values on one line
 * when the line stays within 80 characters. $column = where the value starts on its line.
 */
function encode(mixed $value, int $indent, int $column = 0): string
{
    $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    if ($value instanceof stdClass) $value = (array) $value + ['__object' => true];

    if (is_array($value) && isset($value['__object'])) {
        unset($value['__object']);
        if ($value === []) return '{}';
        $pad = str_repeat(' ', $indent + 2);
        $lines = [];
        foreach ($value as $key => $item) {
            $prefix = $pad . json_encode((string) $key, $flags) . ': ';
            $lines[] = $prefix . encode($item, $indent + 2, strlen($prefix));
        }
        return "{\n" . implode(",\n", $lines) . "\n" . str_repeat(' ', $indent) . '}';
    }

    if (is_array($value)) {
        if ($value === []) return '[]';
        $scalars = array_filter($value, fn ($v) => !is_array($v) && !($v instanceof stdClass));
        if (count($scalars) === count($value)) {
            $inline = '[' . implode(', ', array_map(fn ($v) => json_encode($v, $flags), $value)) . ']';
            if ($column + strlen($inline) + 1 <= 80) return $inline;
        }
        $pad = str_repeat(' ', $indent + 2);
        $lines = array_map(fn ($v) => $pad . encode($v, $indent + 2, strlen($pad)), $value);
        return "[\n" . implode(",\n", $lines) . "\n" . str_repeat(' ', $indent) . ']';
    }

    return json_encode($value, $flags);
}

/** section_layout_aside block -> project_page block */
function migrate(object $layout): object
{
    $data = new stdClass();
    $children = [];

    foreach (($layout->children ?? []) as $slot) {
        if (($slot->component ?? '') !== 'layout_content') continue; // layout_aside: post_short_info + anchor_index
        foreach (($slot->children ?? []) as $block) {
            $name = str_replace('-', '_', (string) ($block->component ?? ''));
            $bd = $block->data ?? new stdClass();

            switch ($name) {
                case 'project_feature_hero':
                case 'project_visual_media_gallery_video':
                    break;

                case 'project_visual_web_gallery':
                case 'project_visual_media_gallery':
                    $text = overview_text($bd);
                    if ($text !== '' && !isset($data->overview)) $data->overview = (object) ['text' => $text];
                    if (($bd->show_video ?? true) === false) $data->videos = false;
                    if (($bd->show_photo ?? true) === false) $data->gallery = false;
                    break;

                case 'block_highlight':
                    $related = related($bd, $block);
                    $children[] = $related ?? $block;
                    break;

                default:
                    $children[] = $block;
            }
        }
    }

    $page = new stdClass();
    $page->component = 'project_page';
    $page->data = $data;
    $page->children = $children;
    return $page;
}

/** First visible project_block_visual_and_paragraph text of a gallery block. */
function overview_text(object $bd): string
{
    foreach (($bd->content_section_primary ?? []) as $item) {
        if (($item->component ?? '') !== 'project_block_visual_and_paragraph') continue;
        if (($item->data->render ?? true) === false) continue;
        $text = trim((string) ($item->data->text ?? ''));
        if ($text !== '') return $text;
    }
    return '';
}

/** A highlight that only lists posts (post-items child) -> related_projects; null for a normal highlight. */
function related(object $bd, object $block): ?object
{
    $kids = $bd->children ?? $block->children ?? [];
    if (!is_array($kids) || count($kids) !== 1 || str_replace('-', '_', (string) ($kids[0]->component ?? '')) !== 'post_items') return null;
    $items = $kids[0]->data ?? new stdClass();

    $data = new stdClass();
    if (trim((string) ($bd->title ?? '')) !== '') $data->title = trim((string) $bd->title);
    $data->id = 'related';
    foreach (['post_type', 'filter_by', 'exclude_by', 'sort', 'exclude_current'] as $key) {
        if (isset($items->$key)) $data->$key = $items->$key;
    }

    $out = new stdClass();
    $out->component = 'related_projects';
    $out->data = $data;
    return $out;
}
