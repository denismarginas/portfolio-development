<?php

trait tab_list_render
{
    public static function render(array $data = []): string
    {
        $itemType = (string) ($data['item_type'] ?? '');
        if ($itemType === '' && !empty($data['items'])) {
            $itemType = (string) ($data['type'] ?? $data['item_type'] ?? '');
        }

        $items = $data['items'] ?? PlatformDataService::get_all_items_from_file($itemType);
        if (!is_array($items) || empty($items)) return '';

        $contentTemplate = $data['tab_item_content_template'] ?? null;
        $labelTemplate = $data['tab_item_label_template'] ?? null;
        $cardTemplate = (string) ($data['card_template'] ?? $data['card'] ?? 'education');

        $tabs = '';
        $renderItems = '';
        foreach ($items as $item) {
            if (($item['settings']['render'] ?? true) === false) continue;

            $itemId = (string) ($item['item_id'] ?? $item['post_id'] ?? '');
            if ($itemId === '') continue;

            $tabs .= self::render_tab($itemId, $item, $labelTemplate);

            $renderItems .= self::render_item($itemId, $item, $contentTemplate, $cardTemplate);
        }

        if ($renderItems === '') return '';

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/template.html', [
            'tabs' => $tabs,
            'items' => $renderItems,
        ]);
    }

    protected static function render_tab(string $itemId, array $item, mixed $labelTemplate): string
    {
        if (is_array($labelTemplate)) {
            $label = self::render_component_spec($labelTemplate, $item);
            if ($label === '') {
                $label = htmlspecialchars(self::item_title($item), ENT_QUOTES, 'UTF-8');
            }
        } else {
            $label = htmlspecialchars(self::item_title($item), ENT_QUOTES, 'UTF-8');
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/parts/tab.html', [
            'item_id' => htmlspecialchars($itemId, ENT_QUOTES, 'UTF-8'),
            'label' => $label,
        ]);
    }

    protected static function render_item(string $itemId, array $item, mixed $contentTemplate, string $cardTemplate): string
    {
        $card = '';
        if (is_array($contentTemplate)) {
            $card = self::render_component_spec($contentTemplate, $item);
        }
        if ($card === '') {
            $card = PlatformComponentRenderer::render('card', [
                'variant' => $cardTemplate,
                'item' => $item,
                'post_current_data' => $item,
            ]);
        }

        return PlatformTemplateRenderer::render(__DIR__ . '/../../html/parts/item.html', [
            'item_id' => htmlspecialchars($itemId, ENT_QUOTES, 'UTF-8'),
            'card' => $card,
        ]);
    }

    protected static function item_title(array $item): string
    {
        $title = $item['data']['seo']['title'] ?? '';
        if (is_string($title) && $title !== '') {
            return $title;
        }
        return (string) ($item['item_id'] ?? $item['post_id'] ?? '');
    }
}