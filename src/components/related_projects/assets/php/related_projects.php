<?php

require_once __DIR__ . '/../../../utility_project_media/assets/php/utility_project_media.php';

/**
 * Related projects (a <section> of its own): the project cards of the old design (post-items +
 * card_post_project with card_post_project_visual_default - logo + thumbnail, title, description),
 * 1 column, 2 from 768px, 3 from 1024px.
 *
 *   { "component": "related_projects", "data": { "filter_by": { "_id": ["agramix-media"] } } }
 *
 * data (all optional):
 *   post_type        string  default "project"
 *   filter_by        object  post-items filter (e.g. { "_id": [...] }); exclude_by array; sort array
 *   exclude_current  bool    leave out the page's own project (default true)
 *   template         object  post-items template (default: card_post_project, visual left / text right)
 *   title            string  section title (default "Related projects"; one project: "Related project")
 *   kicker, text     string  section_head texts
 *   id               string  default "related"
 *   variant          string  section variant (default "panel")
 */
class related_projects
{
    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    /** the card of the old "Related Project" highlight */
    private const TEMPLATE = [
        'component' => 'card_post_project',
        'params' => [
            'card_class' => 'card-post-project',
            'visual_component' => 'card_post_project_visual_default',
            'title_length' => 0,
            'description' => true,
            'description_length' => 0,
            'columns' => [
                'layout' => ['base' => '100', 'sm' => '40-60'],
                'gap' => 'sm',
                'align' => 'center',
            ],
        ],
    ];

    public static function render(array $data = []): string
    {
        $count = self::count($data);
        if ($count === 0) return '';

        // auto columns from the number of projects (utility_grid_column_balancing, like the project galleries):
        // 1 -> full width, 2 -> 2 columns, 3 -> 3, 4 -> 4 (1 + 3 on tablets) ... every row filled
        $grid = '';
        $items = (string) PlatformComponentRenderer::render('post_items', array_merge(
            array_intersect_key($data, array_flip(self::PASSTHROUGH)),
            [
                'class' => 'gap-md show-preview related-projects-listing',
                'post_type' => trim((string) ($data['post_type'] ?? 'project')) ?: 'project',
                'filter_by' => is_array($data['filter_by'] ?? null) ? $data['filter_by'] : [],
                'exclude_by' => $data['exclude_by'] ?? [],
                'exclude_current' => ($data['exclude_current'] ?? true) !== false,
                'sort' => $data['sort'] ?? [],
                'template' => is_array($data['template'] ?? null) ? $data['template'] : self::TEMPLATE,
            ]
        ));
        if (trim($items) === '') return '';
        $items = preg_replace(
            '/<ul class="([^"]*related-projects-listing[^"]*)"/',
            '<ul class="$1"' . str_replace('$', '\\$', (string) PlatformComponentRenderer::value('utility_grid_column_balancing', ['count' => substr_count($items, '<li data-post-id=')])),
            $items,
            1
        ) ?? $items;

        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '' || in_array(strtolower($title), ['related project', 'related projects'], true)) {
            $title = $count === 1
                ? utility_project_media::text('related_title_one', 'Related <em>project</em>')
                : utility_project_media::text('related_title', 'Related <em>projects</em>');
        }
        $head = (string) PlatformComponentRenderer::render('section_head', [
            'kicker' => array_key_exists('kicker', $data) ? trim((string) $data['kicker']) : utility_project_media::text('related_kicker', 'Related'),
            'title' => $title,
            'text' => trim((string) ($data['text'] ?? '')),
        ]);

        return (string) PlatformComponentRenderer::render('section', [
            'id' => array_key_exists('id', $data) ? trim((string) $data['id']) : 'related',
            'class' => 'project-section related-projects-section',
            'variant' => $data['variant'] ?? 'panel',
            'children' => $head . '<div class="related-projects">' . $items . '</div>',
        ]);
    }

    /** How many projects the list will show (for the column count and the title). */
    private static function count(array $data): int
    {
        $posts = PlatformDataService::get_all_posts_from_file(trim((string) ($data['post_type'] ?? 'project')) ?: 'project') ?? [];
        if (!is_array($posts)) return 0;
        $current = (string) ($data['post_current_data']['_id'] ?? '');
        if (($data['exclude_current'] ?? true) !== false && $current !== '') {
            $posts = array_values(array_filter($posts, fn ($p) => is_array($p) && (string) ($p['_id'] ?? '') !== $current));
        }
        return count((array) PlatformComponentRenderer::value('utility_filter', [
            'items' => $posts,
            'filter_by' => is_array($data['filter_by'] ?? null) ? $data['filter_by'] : [],
            'exclude_by' => $data['exclude_by'] ?? [],
        ]));
    }
}
