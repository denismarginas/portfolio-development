<?php

class Footer
{
    public static function render(array $data = []): string
    {
        $jsonGlobalData = get_data_json('data_global_settings', 'data');
        $jsonCategoriesData = get_data_json('data_post_projects_terms', 'data');
        $footerConfig = $jsonGlobalData['theme_active']['footer'] ?? [];

        $linkListHtml = '';

        $block1 = $footerConfig['block_1'] ?? [];
        $linkListHtml .= self::wrap_block(
            FooterLinklist::render([
                'footerBlock' => $block1,
                'pageSlugExtension' => '',
                'skipWrapper' => true,
            ]),
            $block1
        );

        $block2 = $footerConfig['block_2'] ?? [];
        $linkListHtml .= self::wrap_block(
            FooterLinklist::render([
                'footerBlock' => $block2,
                'globalData' => ['categories' => $jsonCategoriesData['categories'] ?? []],
                'pageSlugExtension' => '',
                'skipWrapper' => true,
            ]),
            $block2
        );

        $block3 = $footerConfig['block_3'] ?? [];
        $socialsHtml = self::wrap_block(
            SocialsButtons::render([
                'footerBlock' => $block3,
                'socialsData' => $block3['list'] ?? [],
            ]),
            $block3
        );

        $block4 = $footerConfig['block_4'] ?? [];
        $copyrightsHtml = self::wrap_block(
            Copyrights::render([
                'footerBlock' => $block4,
            ]),
            $block4
        );

        return self::render_template([
            'linklist_html' => $linkListHtml,
            'socials_html' => $socialsHtml,
            'copyrights_html' => $copyrightsHtml,
        ]);
    }

    protected static function wrap_block(string $content, array $blockConfig): string
    {
        $class = $blockConfig['class'] ?? '';
        if ($class === '') {
            return $content;
        }
        return '<div class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" data-motion="transition-fade-1" data-duration="0.5s" style="--duration: 0.5s;">' . $content . '</div>';
    }

    protected static function render_template(array $data): string
    {
        $templatePath = __DIR__ . '/../html/template.html';
        if (!file_exists($templatePath)) {
            return '';
        }

        $html = file_get_contents($templatePath);
        foreach ($data as $key => $value) {
            $html = str_replace('{{ ' . $key . ' }}', $value, $html);
        }

        return $html;
    }
}
