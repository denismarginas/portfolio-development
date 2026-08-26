<?php

class experience_knowledge
{
    public static function render(array $data = []): string
    {
        $exp = $data['experience'] ?? PlatformDataService::get_data('content_experience');
        if (!is_array($exp) || empty($exp)) return '';

        $portrait = self::render_portrait($exp['media']['portrait']['img'] ?? '');
        $main = self::render_main($exp);
        $side = self::render_side($exp);

        return PlatformTemplateRenderer::render([
            'portrait' => $portrait,
            'main' => $main,
            'side' => $side,
        ]);
    }

    private static function render_portrait(string $src): string
    {
        if ($src === '') return '';
        $img = PlatformComponentRenderer::render('image', ['src' => $src, 'alt' => 'Portrait', 'class' => 'portrait-img']);
        $dots = PlatformComponentRenderer::render('svg', ['icon' => 'dots-graphic', 'class' => 'portrait-dots']);
        return '<div class="portrait">' . $img . '<div class="portrait-graphic">' . $dots . '</div></div>';
    }

    private static function render_main(array $exp): string
    {
        $html = '';
        $title = (string) ($exp['title'] ?? '');
        if ($title !== '') $html .= '<h2>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2>';

        $textItems = $exp['knowledge_lists_text']['text_items'] ?? [];
        if (!empty($textItems) && is_array($textItems)) {
            foreach ($textItems as $t) $html .= '<p>' . htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        $lists = $exp['knowledge_lists_text']['list_items'] ?? [];
        if (!empty($lists) && is_array($lists)) {
            $html .= '<div class="knowledge-lists">';
            foreach ($lists as $cat) {
                $html .= '<div class="knowledge-group"><p class="knowledge-title">' . htmlspecialchars((string)($cat['title'] ?? ''), ENT_QUOTES, 'UTF-8') . '</p><ul>';
                foreach ($cat['text_list'] ?? [] as $item) $html .= '<li>' . htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') . '</li>';
                $html .= '</ul></div>';
            }
            $html .= '</div>';
        }

        $icons = $exp['knowledge_lists_items'] ?? [];
        if (!empty($icons) && is_array($icons)) {
            $html .= '<ul class="knowledge-icons">';
            foreach ($icons as $ic) {
                $svg = PlatformComponentRenderer::render('svg', ['icon' => (string)($ic['svg'] ?? ''), 'class' => 'knowledge-icon']);
                $url = (string)($ic['external_url'] ?? '');
                $text = htmlspecialchars((string)($ic['text'] ?? ''), ENT_QUOTES, 'UTF-8');
                $item = $svg . '<span>' . $text . '</span>';
                $html .= $url !== '' ? '<li><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener">' . $svg . '</a></li>' : '<li>' . $svg . '</li>';
            }
            $html .= '</ul>';
        }

        $buttons = $exp['buttons'] ?? [];
        if (!empty($buttons) && is_array($buttons)) {
            $html .= '<div class="actions">';
            foreach ($buttons as $btn) {
                $html .= PlatformComponentRenderer::render('button', ['text' => (string)($btn['text'] ?? ''), 'link' => $btn['_id'] ?? '', 'svg' => (string)($btn['svg'] ?? ''), 'class' => 'btn btn-primary']);
            }
            $html .= '</div>';
        }

        return $html;
    }

    private static function render_side(array $exp): string
    {
        $html = '';
        $textList = $exp['text_list'] ?? [];
        if (!empty($textList) && is_array($textList)) {
            $html .= '<div class="text-list">';
            foreach ($textList as $p) $html .= '<p>' . htmlspecialchars((string)$p, ENT_QUOTES, 'UTF-8') . '</p>';
            $html .= '</div>';
        }
        $banner = $exp['media']['banner']['img'] ?? '';
        if ($banner !== '') {
            $img = PlatformComponentRenderer::render('image', [
                'src' => $banner,
                'alt' => 'Banner',
                'class' => 'banner-img',
                'attributes' => ['data-popup' => 'true'],
            ]);
            $html .= '<div class="banner">' . $img . '</div>';
        }
        return $html;
    }
}