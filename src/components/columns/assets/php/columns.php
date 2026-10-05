<?php

/**
 * Side-by-side columns. Layout only: put it inside a "section" (or any wrapper) for the <section><container>.
 *
 *   {
 *     "component": "section",
 *     "children": [
 *       {
 *         "component": "columns",
 *         "data": {
 *           "layout": { "base": "100", "md": "60-40", "xl": "70-30" },
 *           "gap":    { "base": "sm",  "md": "md",    "xl": "xl" }
 *         },
 *         "children": [
 *           { "component": "form", "data": { ... } },               // one component = one column
 *           { "component": "column", "children": [                 // "column" = several components in one column
 *               { "component": "text_block", "data": { ... } },
 *               { "component": "resume_listing", "data": { ... } }
 *           ] }
 *         ]
 *       }
 *     ]
 *   }
 *
 * data:
 *   layout          string | object  "100" | "50-50" | "60-40" | "40-60" | "70-30" | "30-70" | "33-33-33"
 *                   - string: that layout from "stack_below" up, one column ("100") below it
 *                   - object: per screen size, mobile-first; each value applies from that size up
 *                     until the next one: { "base": …, "xs": …, "sm": …, "md": …, "lg": …, "xl": …, "2xl": … }
 *                     (theme breakpoints: xs 320, sm 480, md 768, lg 1024, xl 1280, 2xl 1440 px)
 *   stack_below     string  only for a string layout: "md" | "lg" (default) | "xl"
 *   gap             string | object  "0" | "xxs" | "xs" | "sm" | "md" | "lg" | "xl" (default) | "2xl" | "3xl",
 *                   or per screen size like layout: { "base": "sm", "md": "md", "xl": "xl" }
 *   align           string  vertical alignment: "start" (default) | "center" | "end" | "stretch"
 *   reverse_stacked bool    while in one column ("100"), show the last column first (default false)
 *   class, id       string  optional
 *
 * Uses the theme classes cols-[<bp>-]<layout> (utilities/grid.scss) and gap-[<bp>-]<size> (utilities/gap.scss).
 */
class columns
{
    private const LAYOUTS = ['100', '50-50', '60-40', '40-60', '70-30', '30-70', '33-33-33'];
    private const BREAKPOINTS = ['base', 'xs', 'sm', 'md', 'lg', 'xl', '2xl'];
    private const GAPS = ['0', 'xxs', 'xs', 'sm', 'md', 'lg', 'xl', '2xl', '3xl'];
    private const ALIGNS = ['start', 'center', 'end', 'stretch'];
    private const PASSTHROUGH = ['post_current_data', 'global_content_path', 'global_img_path', 'global_vid_path'];

    public static function render(array $data = []): string
    {
        $columnsHtml = self::columns($data);
        if ($columnsHtml === '') return '';

        $layouts = self::responsive($data['layout'] ?? '50-50', self::LAYOUTS, '50-50', $data['stack_below'] ?? 'lg', true);
        $gaps = self::responsive($data['gap'] ?? 'xl', self::GAPS, 'xl', null, false);
        $a = is_scalar($data['align'] ?? null) ? strtolower(trim((string) $data['align'])) : '';
        $align = in_array($a, self::ALIGNS, true) ? $a : 'start';

        $class = is_scalar($data['class'] ?? null) ? trim((string) $data['class']) : '';
        $id = is_scalar($data['id'] ?? null) ? trim((string) $data['id']) : '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../html/template.html', [
            'grid_class' => self::classes('cols', $layouts),
            'gap_class' => self::classes('gap', $gaps),
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'layout' => self::e(implode(' ', array_map(fn ($bp, $l) => $bp . ':' . $l, array_keys($layouts), $layouts))),
            'align' => $align,
            'reverse_attr' => !empty($data['reverse_stacked']) ? ' data-reverse-stacked="true" data-stack-below="' . self::stack_below($layouts) . '"' : '',
            'columns' => $columnsHtml,
        ]);
    }

    /** Children -> <div class="column">…</div> (a "column" child groups several components). */
    private static function columns(array $data): string
    {
        $children = $data['children'] ?? [];
        if (!is_array($children) || empty($children)) return '';

        $passthrough = array_intersect_key($data, array_flip(self::PASSTHROUGH));

        $html = '';
        foreach ($children as $child) {
            if (!is_array($child)) continue;
            $name = str_replace('-', '_', (string) ($child['component'] ?? ''));
            if ($name === '') continue;

            $blocks = $name === 'column'
                ? (array) ($child['children'] ?? $child['data']['children'] ?? [])
                : [$child];

            $inner = trim((string) section::render_children(array_merge($passthrough, ['children' => $blocks])));
            if ($inner === '') continue;

            $class = $name === 'column' && is_scalar($child['data']['class'] ?? null) ? trim((string) $child['data']['class']) : '';
            $html .= '<div class="column' . ($class !== '' ? ' ' . self::e($class) : '') . '">' . $inner . '</div>';
        }
        return $html;
    }

    /**
     * Normalizes a value or a per-screen object to [ breakpoint => value ] (mobile-first order).
     * String layout + stack_below -> [ base => "100", <stack_below> => layout ].
     */
    private static function responsive(mixed $value, array $allowed, string $default, ?string $stackBelow, bool $isLayout): array
    {
        if (is_array($value)) {
            $out = [];
            foreach (self::BREAKPOINTS as $bp) {
                $raw = $value[$bp] ?? ($bp === 'base' ? ($value['default'] ?? '') : '');
                $v = is_scalar($raw) ? strtolower(trim((string) $raw)) : '';
                if ($v !== '' && in_array($v, $allowed, true)) $out[$bp] = $v;
            }
            if (!isset($out['base'])) $out = ['base' => $isLayout ? '100' : $default] + $out;
            return $out;
        }

        $v = is_scalar($value) ? strtolower(trim((string) $value)) : '';
        if (!in_array($v, $allowed, true)) $v = $default;
        if (!$isLayout || $v === '100') return ['base' => $v];

        $s = is_scalar($stackBelow) ? strtolower(trim((string) $stackBelow)) : '';
        $bp = in_array($s, ['md', 'lg', 'xl'], true) ? $s : 'lg';
        return ['base' => '100', $bp => $v];
    }

    /** First breakpoint where the layout is no longer one column ("none" = always one column). */
    private static function stack_below(array $layouts): string
    {
        foreach ($layouts as $bp => $layout) {
            if ($layout !== '100') return $bp;
        }
        return 'none';
    }

    /** [ base => "sm", md => "md" ] -> "gap-sm gap-md-md" */
    private static function classes(string $prefix, array $values): string
    {
        $classes = [];
        foreach ($values as $bp => $value) {
            $classes[] = $prefix . '-' . ($bp === 'base' ? '' : $bp . '-') . $value;
        }
        return implode(' ', $classes);
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
