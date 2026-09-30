<?php

/**
 * Post visual + paragraph with #tags (the old section_media_project ".dm-post-details-grid":
 * logo on the left, description on the right). 30% / 70%, stacked on small screens.
 * Nothing is generated: the text and the tags come from data.
 *
 * Built from existing components:
 *   post_short_info_visual  (the visual)
 *   block_paragraph         (the text card)  + block_tags as its child (the #tag pills)
 *
 *   {
 *     "component": "project_block_visual_and_paragraph",
 *     "data": {
 *       "text": "Experience the artistry behind ...",
 *       "tags": [
 *         { "text": "photo", "link": "#photo" },
 *         { "text": "video", "link": "#video" }
 *       ]
 *     }
 *   }
 *
 * data:
 *   text             string    paragraph (block_paragraph rules: <br>, <b>, <strong>, <i>, <em>, <u> are kept)
 *   tags             array     block_tags "tags" ({ "text", "link"? } objects or plain strings)
 *   show_visual      bool      default true
 *   visual           object    extra data for post_short_info_visual (e.g. {"show_preview": false})
 *   visual_position  string    "left" (default) | "right" (on small screens the visual is always on top)
 *   post_id          string    optional, show another post's visual (default: current post)
 *   class, id        string    optional
 *
 * Renders nothing when there is no visual, no text and no tags.
 */
class project_block_visual_and_paragraph
{
    private const PASSTHROUGH = ['post_current_data', 'post_id', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $visual = '';
        if (post_short_info_post::flag($data, 'show_visual', true)) {
            $visual = (string) PlatformComponentRenderer::render('post_short_info_visual', array_merge(
                self::object($data['visual'] ?? []),
                $shared,
                ['class' => 'project-block-visual-and-paragraph-visual']
            ));
        }

        $paragraph = (string) PlatformComponentRenderer::render('block_paragraph', array_merge($shared, [
            'text' => (string) ($data['text'] ?? ''),
            'class' => 'project-block-visual-and-paragraph-content',
            'children' => [
                ['component' => 'block_tags', 'data' => ['tags' => $data['tags'] ?? []]],
            ],
        ]));

        if (trim($visual . $paragraph) === '') return '';

        $classes = [];
        if (trim($visual) === '') $classes[] = 'project-block-visual-and-paragraph-no-visual';
        if (strtolower(trim((string) ($data['visual_position'] ?? 'left'))) === 'right') {
            $classes[] = 'project-block-visual-and-paragraph-visual-right';
        }
        $extra = trim((string) ($data['class'] ?? ''));
        if ($extra !== '') $classes[] = $extra;

        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render([
            'extra_class' => $classes ? ' ' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') : '',
            'id_attr' => $id !== '' ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : '',
            'visual' => $visual,
            'paragraph' => $paragraph,
        ]);
    }

    /** Object option; the editor stores objects as JSON text. */
    private static function object(mixed $value): array
    {
        if (is_string($value)) $value = json_decode($value, true);
        return is_array($value) ? $value : [];
    }
}
