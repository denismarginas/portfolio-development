<?php

/**
 *   { "component": "form", "data": { "form": "contact-form" } }
 *
 * data:
 *   form        string  _id of the form item (default: first item of the file)
 *   type        string  item file (default "form" -> data_items_form.json)
 *   title       string  heading (default: the item's data.seo.title; "" hides it)
 *   title_tag   string  "h3" (default) | "h2" | "h4"
 *   class, id   string  optional
 *
 * Item (data_items_form.json):
 *   data.seo.title
 *   data.type        "steps" | "default"
 *   data.external    { type: "google-forms", url, submit_note, link_text }
 *   data.fields      final fields sent to the external form:
 *                    [{ name, type (text|email|tel|textarea), placeholder, external_name ("entry.123"), compose ("lines") }]
 *                    "default" type: shown as the form; "steps" type: hidden, filled from the steps
 *   data.steps       [{ id, banner (image) | icon (svg) + label, fields: [...], buttons: [...] }]
 *     step field     { name, type (text|email|tel|textarea|radio), placeholder, label, required,
 *                      target (final field name), options (radio): [{ value, label, svg, step }] }
 *     button         { type: "next" | "prev" | "submit", text, step ("2" or "@<radio name>" = the chosen option's step) }
 */
class form
{
    private const HTML_DIR = __DIR__ . '/../html/';
    private const INPUT_TYPES = ['text', 'email', 'tel', 'url', 'number'];
    private const TITLE_TAGS = ['h2', 'h3', 'h4'];

    public static function render(array $data = []): string
    {
        $item = self::item($data);
        if ($item === null) return '';

        $d = is_array($item['data'] ?? null) ? $item['data'] : [];
        $formId = 'form-' . preg_replace('/[^a-z0-9\-_]/i', '-', (string) ($item['_id'] ?? 'form'));
        $steps = is_array($d['steps'] ?? null) ? array_values($d['steps']) : [];
        $isSteps = strtolower((string) ($d['type'] ?? '')) === 'steps' && !empty($steps);
        $fields = is_array($d['fields'] ?? null) ? $d['fields'] : [];
        $external = is_array($d['external'] ?? null) ? $d['external'] : [];

        $stepsHtml = $isSteps
            ? self::steps($steps, $formId)
            : self::step(['id' => '1', 'fields' => $fields, 'buttons' => [['type' => 'submit', 'text' => (string) ($d['submit_text'] ?? 'Submit')]]], $formId, true, false);

        $title = array_key_exists('title', $data) ? trim((string) $data['title']) : trim((string) ($d['seo']['title'] ?? ''));
        $tag = strtolower((string) ($data['title_tag'] ?? 'h3'));
        if (!in_array($tag, self::TITLE_TAGS, true)) $tag = 'h3';

        $class = trim((string) ($data['class'] ?? ''));
        $id = trim((string) ($data['id'] ?? ''));

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'template.html', [
            'extra_class' => $class !== '' ? ' ' . self::e($class) : '',
            'id_attr' => $id !== '' ? ' id="' . self::e($id) . '"' : '',
            'title' => $title !== '' ? '<' . $tag . ' class="form-title title-divider">' . self::e($title) . '</' . $tag . '>' : '',
            'form_id' => self::e($formId),
            'form_type' => $isSteps ? 'steps' : 'default',
            'external_type' => self::e((string) ($external['type'] ?? '')),
            'external_url' => self::e((string) ($external['url'] ?? '')),
            'final_fields' => $isSteps ? self::final_fields($fields) : '',
            'steps' => $stepsHtml,
            'submit_note' => self::e((string) ($external['submit_note'] ?? 'Thank you! Your message is ready to be sent.')),
            'link_text' => self::e((string) ($external['link_text'] ?? 'Proceed to the form')),
        ]);
    }

    private static function item(array $data): ?array
    {
        $type = trim((string) ($data['type'] ?? '')) ?: 'form';
        $items = PlatformDataService::get_all_items_from_file($type) ?? [];
        if (!is_array($items)) return null;

        $wanted = trim((string) ($data['form'] ?? $data['item'] ?? ''));
        foreach ($items as $item) {
            if (!is_array($item) || ($item['settings']['render'] ?? true) === false) continue;
            if ($wanted === '' || ($item['_id'] ?? '') === $wanted) return $item;
        }
        return null;
    }

    /** Hidden inputs filled by the JS from the steps (one per external field). */
    private static function final_fields(array $fields): string
    {
        $html = '';
        foreach ($fields as $field) {
            if (!is_array($field) || trim((string) ($field['name'] ?? '')) === '') continue;
            $html .= '<input type="hidden" class="form-final" data-final="' . self::e((string) $field['name']) . '"'
                . ' data-external-name="' . self::e((string) ($field['external_name'] ?? '')) . '"'
                . ' data-compose="' . self::e((string) ($field['compose'] ?? '')) . '" value="">';
        }
        return $html;
    }

    private static function steps(array $steps, string $formId): string
    {
        $html = '';
        foreach ($steps as $i => $step) {
            if (is_array($step)) $html .= self::step($step, $formId, $i === 0, true);
        }
        return $html;
    }

    private static function step(array $step, string $formId, bool $first, bool $isSteps): string
    {
        $fieldsHtml = '';
        foreach ((array) ($step['fields'] ?? []) as $field) {
            if (is_array($field)) $fieldsHtml .= self::field($field, $formId, $isSteps);
        }

        $buttonsHtml = '';
        foreach ((array) ($step['buttons'] ?? []) as $button) {
            if (is_array($button)) $buttonsHtml .= self::button($button);
        }

        return PlatformTemplateRenderer::render(self::HTML_DIR . 'parts/step.html', [
            'step_id' => self::e((string) ($step['id'] ?? '')),
            'hidden_attr' => $first ? '' : ' hidden',
            'header' => self::header($step),
            'fields' => $fieldsHtml,
            'buttons' => $buttonsHtml,
        ]);
    }

    private static function header(array $step): string
    {
        $banner = trim((string) ($step['banner'] ?? ''));
        if ($banner !== '') {
            $img = (string) PlatformComponentRenderer::render('image', ['src' => $banner, 'alt' => (string) ($step['label'] ?? ''), 'class' => 'form-step-banner', 'lazy' => true]);
            if (trim($img) !== '') return '<div class="form-step-header has-banner">' . $img . '</div>';
        }

        $icon = trim((string) ($step['icon'] ?? ''));
        $label = trim((string) ($step['label'] ?? ''));
        if ($icon === '' && $label === '') return '';

        $iconHtml = $icon !== '' ? (string) PlatformComponentRenderer::render('svg', ['icon' => $icon, 'class' => 'form-step-icon']) : '';
        return '<legend class="form-step-header">' . $iconHtml . ($label !== '' ? '<span class="form-step-label">' . self::e($label) . '</span>' : '') . '</legend>';
    }

    private static function field(array $field, string $formId, bool $isSteps): string
    {
        $name = trim((string) ($field['name'] ?? ''));
        if ($name === '') return '';

        $type = strtolower((string) ($field['type'] ?? 'text'));
        $id = $formId . '-' . $name;
        $placeholder = (string) ($field['placeholder'] ?? '');
        $label = (string) ($field['label'] ?? $placeholder);
        $required = !empty($field['required']) ? ' required' : '';

        // steps: data-target = the final field it fills; default: the field itself is final
        $target = $isSteps ? (string) ($field['target'] ?? '') : $name;
        $dataAttrs = ' data-target="' . self::e($target) . '" data-label="' . self::e($label) . '"';
        if (!$isSteps) {
            $dataAttrs .= ' data-external-name="' . self::e((string) ($field['external_name'] ?? '')) . '"'
                . ' data-compose="' . self::e((string) ($field['compose'] ?? '')) . '"';
        }

        if ($type === 'radio') {
            return self::radio($field, $id, $name, $label, $required, $dataAttrs);
        }

        $common = ' id="' . self::e($id) . '" name="' . self::e($name) . '" placeholder="' . self::e($placeholder) . '" aria-label="' . self::e($label) . '"' . $required . $dataAttrs;

        $control = $type === 'textarea'
            ? '<textarea class="form-control" rows="5"' . $common . '></textarea>'
            : '<input class="form-control" type="' . (in_array($type, self::INPUT_TYPES, true) ? $type : 'text') . '"' . $common
                . self::autocomplete($type, (string) ($field['target'] ?? $name)) . '>';

        return '<div class="form-field" data-type="' . self::e($type) . '">' . $control . '</div>';
    }

    private static function radio(array $field, string $id, string $name, string $label, string $required, string $dataAttrs): string
    {
        $options = '';
        foreach ((array) ($field['options'] ?? []) as $i => $option) {
            if (!is_array($option)) $option = ['value' => (string) $option];
            $value = trim((string) ($option['value'] ?? ''));
            if ($value === '') continue;

            $svg = trim((string) ($option['svg'] ?? ''));
            $icon = $svg !== '' ? (string) PlatformComponentRenderer::render('svg', ['icon' => $svg, 'class' => 'form-option-icon']) : '';
            $step = trim((string) ($option['step'] ?? ''));

            $options .= '<label class="form-option" for="' . self::e($id . '-' . $i) . '">'
                . '<input type="radio" id="' . self::e($id . '-' . $i) . '" name="' . self::e($name) . '" value="' . self::e($value) . '"'
                . ($step !== '' ? ' data-step="' . self::e($step) . '"' : '') . ($i === 0 ? $required : '') . $dataAttrs . '>'
                . $icon . '<span class="form-option-text">' . self::e((string) ($option['label'] ?? $value)) . '</span></label>';
        }
        if ($options === '') return '';

        return '<div class="form-field" data-type="radio" role="radiogroup" aria-label="' . self::e($label) . '">' . $options . '</div>';
    }

    private static function button(array $button): string
    {
        $type = strtolower((string) ($button['type'] ?? 'next'));
        $text = self::e((string) ($button['text'] ?? ucfirst($type)));
        $step = trim((string) ($button['step'] ?? ''));
        $stepAttr = $step !== '' ? ' data-step="' . self::e($step) . '"' : '';

        return match ($type) {
            'submit' => '<button class="btn btn-primary form-submit" type="submit">' . $text . '</button>',
            'prev' => '<button class="btn btn-outline form-prev" type="button"' . $stepAttr . '>' . $text . '</button>',
            default => '<button class="btn btn-primary form-next" type="button"' . $stepAttr . '>' . $text . '</button>',
        };
    }

    private static function autocomplete(string $type, string $target): string
    {
        $map = ['name' => 'name', 'email' => 'email', 'phone' => 'tel'];
        if (isset($map[$target])) return ' autocomplete="' . $map[$target] . '"';
        return $type === 'email' ? ' autocomplete="email"' : '';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
