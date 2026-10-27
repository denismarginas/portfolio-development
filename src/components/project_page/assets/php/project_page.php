<?php

require_once __DIR__ . '/../../../utility_project_media/assets/php/utility_project_media.php';

/**
 * The whole project page. Most of it comes from the post data; "children" add the project's own blocks.
 *
 *   "content": [
 *     {
 *       "component": "project_page",
 *       "data": { "overview": { "text": "Online shop made in Prestashop 1.7 ..." } },
 *       "children": [
 *         { "component": "block_highlight", "data": { "title": "3D - Tire Animation", "slider": "highlights/...", ... } },
 *         { "component": "related_projects", "data": { "filter_by": { "_id": ["agramix-media"] } } }
 *       ]
 *     }
 *   ]
 *
 * data (all optional):
 *   hero        object|false  project_hero params, false = no hero
 *   marquee     object|false  taxonomy_marquee params, false = none
 *   nav         object|false  section_nav params (its items are the sections that rendered), false = none
 *   overview    object|false  project_overview params (text, title, quote, kicker ...), false = none
 *   screens     object|false  screens_viewer params, false = none
 *   brand       object|false  media_bento_grid params (default dir web/media-website), false = none
 *   gallery     object|false  media_gallery_filterable params (default dir media), false = none
 *   videos      object|false  video_playlist params, false = none
 *   sections    string[]      order of the automatic sections (default: overview, screens, brand, gallery, videos);
 *                             "children" marks where the children go (default: after them)
 *   case_study_layout bool    block_highlight children get "layout": "case_study" (alternating sides) unless they
 *                             set a layout (default true)
 *
 * children: any components. Known section components render their own <section>; others are wrapped in one.
 * A section renders only when it has content (no screenshots = no Screens section, ...).
 * Nav labels: data_content_post_projects.json -> "project_page" (nav_overview, nav_screens, ...), else the defaults.
 */
class project_page
{
    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    /** automatic section => [component, default id, nav label key, nav label] */
    private const SECTIONS = [
        'overview' => ['project_overview', 'overview', 'nav_overview', 'Overview'],
        'screens' => ['screens_viewer', 'screens', 'nav_screens', 'Screens'],
        'brand' => ['media_bento_grid', 'brand', 'nav_brand', 'Brand'],
        'gallery' => ['media_gallery_filterable', 'gallery', 'nav_gallery', 'Gallery'],
        'videos' => ['video_playlist', 'videos', 'nav_videos', 'Videos'],
    ];
    private const DEFAULT_ORDER = ['overview', 'screens', 'brand', 'gallery', 'videos', 'children'];

    /** children that render their own <section> (others are wrapped in a project section) */
    private const SECTION_COMPONENTS = [
        'project_overview', 'screens_viewer', 'media_bento_grid', 'media_gallery_filterable', 'video_playlist',
        'related_projects', 'section', 'section_layout_aside', 'taxonomy_marquee', 'project_hero',
    ];

    /** entrance animations (used when data_settings_transitions.json has no rule for the component) */
    private const TRANSITIONS = [
        'project_overview' => ['effect' => 'none', 'parts' => [
            ['target' => '.project-overview-intro', 'effect' => 'fade slide-bottom', 'distance' => '24px'],
            ['target' => '.project-overview-body', 'effect' => 'fade slide-bottom', 'distance' => '24px', 'delay' => 120],
        ]],
        'screens_viewer' => ['effect' => 'none', 'parts' => [
            ['target' => '.section-head', 'effect' => 'fade slide-bottom', 'distance' => '20px'],
            ['target' => '.screens-viewer', 'effect' => 'fade slide-bottom', 'distance' => '24px', 'delay' => 120],
        ]],
        'media_bento_grid' => ['effect' => 'none', 'parts' => [
            ['target' => '.section-head', 'effect' => 'fade slide-bottom', 'distance' => '20px'],
            ['target' => '.media-gallery-filterable-grid', 'effect' => 'fade slide-bottom', 'distance' => '24px', 'delay' => 100],
        ]],
        'media_gallery_filterable' => ['effect' => 'none', 'parts' => [
            ['target' => '.section-head', 'effect' => 'fade slide-bottom', 'distance' => '20px'],
            ['target' => '.media-gallery-filterable-filters', 'effect' => 'fade', 'delay' => 100],
        ]],
        'video_playlist' => ['effect' => 'none', 'parts' => [
            ['target' => '.section-head', 'effect' => 'fade slide-bottom', 'distance' => '20px'],
            ['target' => '.video-playlist', 'effect' => 'fade slide-bottom', 'distance' => '24px', 'delay' => 120],
        ]],
        'related_projects' => ['effect' => 'none', 'parts' => [
            ['target' => '.section-head', 'effect' => 'fade slide-bottom', 'distance' => '20px'],
            ['target' => '.related-projects-listing > li', 'effect' => 'fade slide-bottom', 'distance' => '24px', 'stagger' => 100, 'delay' => 100],
        ]],
    ];

    public static function render(array $data = []): string
    {
        $post = is_array($data['post_current_data'] ?? null) ? $data['post_current_data'] : [];
        if (empty($post)) return '';
        $shared = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $hero = self::part($data, 'hero', 'project_hero', $shared);
        $marquee = self::part($data, 'marquee', 'taxonomy_marquee', $shared);

        // sections first: the nav lists only the ones that rendered
        $sections = '';
        $navItems = [];
        $order = is_array($data['sections'] ?? null) ? $data['sections'] : self::DEFAULT_ORDER;
        if (!in_array('children', $order, true)) $order[] = 'children';
        foreach ($order as $key) {
            if ($key === 'children') {
                [$html, $items] = self::children($data, $shared);
                $sections .= $html;
                $navItems = array_merge($navItems, $items);
                continue;
            }
            if (!isset(self::SECTIONS[$key]) || ($data[$key] ?? []) === false) continue;
            [$component, $id, $labelKey, $label] = self::SECTIONS[$key];
            $params = array_merge(['id' => $id], is_array($data[$key] ?? null) ? $data[$key] : [], $shared);
            $html = self::section($component, $params);
            if (trim($html) === '') continue;
            $sections .= $html;
            $navItems[] = ['id' => (string) $params['id'], 'text' => utility_project_media::text($labelKey, $label)];
        }

        $nav = '';
        if (($data['nav'] ?? []) !== false && count($navItems) > 1) {
            $navParams = is_array($data['nav'] ?? null) ? $data['nav'] : [];
            $nav = (string) PlatformComponentRenderer::render('section_nav', array_merge(['items' => $navItems], $navParams, $shared));
        }

        return PlatformTemplateRenderer::render([
            'kind' => utility_project_media::screens($post, 'desktop') !== [] ? 'website' : 'media',
            'style' => (string) PlatformComponentRenderer::value('utility_appearance_colors', ['post_current_data' => $post, 'output' => 'attribute']),
            'hero' => $hero,
            'marquee' => $marquee,
            'nav' => $nav,
            'sections' => $sections,
        ]);
    }

    /** hero / marquee: params object, false = off. */
    private static function part(array $data, string $key, string $component, array $shared): string
    {
        if (($data[$key] ?? []) === false) return '';
        $params = is_array($data[$key] ?? null) ? $data[$key] : [];
        return (string) PlatformComponentRenderer::render($component, array_merge($params, $shared));
    }

    /** One section component, with its entrance animation (settings rule, else the default above). */
    private static function section(string $component, array $params): string
    {
        if (PlatformComponentRenderer::get_component_config($component) === null) return '';
        if (!array_key_exists('transition', $params)) {
            $rule = PlatformComponentRenderer::transition_rule($component) ?? (self::TRANSITIONS[$component] ?? null);
            if ($rule !== null) $params['transition'] = $rule;
        }
        return (string) PlatformComponentRenderer::render($component, $params);
    }

    /**
     * Children: block_highlight -> case study layout (alternating), related_projects / other section
     * components as they are, anything else inside a project section.
     * @return array{0:string, 1:array} html + nav items
     */
    private static function children(array $data, array $shared): array
    {
        $children = $data['children'] ?? [];
        if (!is_array($children)) return ['', []];

        $caseLayout = ($data['case_study_layout'] ?? true) !== false;
        $html = '';
        $nav = [];
        $caseIndex = 0;
        $caseNavAdded = false;
        $relatedCount = 0;

        foreach ($children as $child) {
            if (!is_array($child)) continue;
            $name = str_replace('-', '_', (string) ($child['component'] ?? ''));
            if ($name === '') continue;
            $params = array_merge(is_array($child['data'] ?? null) ? $child['data'] : [], $shared, [
                'children' => $child['children'] ?? $child['data']['children'] ?? [],
            ]);

            if ($name === 'block_highlight' && $caseLayout && !isset($params['layout'])) {
                $params['layout'] = 'case_study';
                if (!isset($params['flip'])) $params['flip'] = $caseIndex % 2 === 1;
            }

            if ($name === 'block_highlight' && ($params['layout'] ?? '') === 'case_study') {
                $params['variant'] = $params['variant'] ?? ($caseIndex % 2 === 1 ? 'panel' : '');
                $out = self::section($name, $params);
                if (trim($out) === '') continue;
                if (!$caseNavAdded && preg_match('/\sid="([^"]+)"/', $out, $m)) {
                    $nav[] = ['id' => html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'), 'text' => utility_project_media::text('nav_case_studies', 'Case studies')];
                    $caseNavAdded = true;
                }
                $caseIndex++;
                $html .= $out;
                continue;
            }

            if (in_array($name, self::SECTION_COMPONENTS, true)) {
                // a second related_projects block gets an id of its own (related-2 ...)
                if ($name === 'related_projects' && $relatedCount > 0 && in_array((string) ($params['id'] ?? 'related'), ['', 'related'], true)) {
                    $params['id'] = 'related-' . ($relatedCount + 1);
                }
                $out = self::section($name, $params);
                if (trim($out) === '') continue;
                if ($name === 'related_projects') {
                    if ($relatedCount === 0 && preg_match('/\sid="([^"]+)"/', $out, $m)) {
                        $nav[] = ['id' => html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'), 'text' => utility_project_media::text('nav_related', 'Related')];
                    }
                    $relatedCount++;
                }
                $html .= $out;
                continue;
            }

            // any other component: inside a project section (site-wide transition rule like other content)
            if (!array_key_exists('transition', $params) && ($rule = PlatformComponentRenderer::transition_rule($name)) !== null) {
                $params['transition'] = $rule;
            }
            $out = (string) PlatformComponentRenderer::render($name, $params);
            if (trim($out) === '') continue;
            $html .= (string) PlatformComponentRenderer::render('section', ['class' => 'project-section project-section-block', 'children' => $out]);
        }

        return [$html, $nav];
    }
}
