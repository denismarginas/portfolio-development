<?php

class ContactForm
{
    public static function render(array $data = []): string
    {
        $fields = $data['fields'] ?? [];
        if (empty($fields)) {
            $personal = DataService::get_personal_data();
            $fields = $personal['form']['fields'] ?? [];
        }

        $externalUrl = $data['external_form_url'] ?? '';
        if (empty($externalUrl)) {
            $personal = DataService::get_personal_data();
            $externalUrl = $personal['form']['external_form_url'] ?? '';
        }

        $formFields = '';
        foreach ($fields as $field) {
            $formFields .= self::render_field($field, $externalUrl);
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');

        return str_replace('{{ form_fields }}', $formFields, $template);
    }

    protected static function render_field(array $field, string $externalUrl): string
    {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $value = $field['value'] ?? '';
        $step = $field['step'] ?? '';

        $externAttr = '';
        if (!empty($field['field_name_extern'])) {
            $externAttr = ' field-name-extern="' . htmlspecialchars($field['field_name_extern']) . '"';
        }
        $stepAttr = '';
        if (!empty($field['field-name-step'])) {
            $stepAttr = ' field-name-step="' . htmlspecialchars($field['field-name-step']) . '"';
        }

        return match ($type) {
            'text', 'input' => sprintf(
                '<input id="%s" type="text" name="%s" placeholder="%s" value=""%s%s>',
                $name, $name, $placeholder, $externAttr, $stepAttr
            ),
            'message', 'textarea' => sprintf(
                '<textarea id="%s" type="text" name="%s" placeholder="%s" value=""%s%s></textarea>',
                $name, $name, $placeholder, $externAttr, $stepAttr
            ),
            'send', 'submit' => self::render_submit($name, $value, $externalUrl),
            'next', 'step' => sprintf(
                '<div><button class="form-next-step" id="%s" data-button="primary" data-action="next-step" data-step="%s" type="button">%s</button></div>',
                $name, $step, $value
            ),
            'prev' => sprintf(
                '<div><button class="form-prev-step" id="%s" data-button="primary" data-action="prev-step" data-step="%s" type="button">%s</button></div>',
                $name, $step, $value
            ),
            'html', 'HTML' => '<div class="content">' . ($field['content'] ?? '') . '</div>',
            'code', 'CODE' => '<div class="content">' . Helpers::execute_php_in_string($field['content'] ?? '') . '</div>',
            default => ''
        };
    }

    protected static function render_submit(string $name, string $value, string $externalUrl): string
    {
        $html = '<div><button class="form-submit" id="' . $name . '" data-button="primary" data-callback="onSubmit" data-action="submit" type="submit">' . htmlspecialchars($value) . '</button>';
        if (!empty($externalUrl)) {
            $html .= '<a class="external-form-submit" href="' . htmlspecialchars($externalUrl) . '" data-button="primary" target="_blank">' . htmlspecialchars($value) . '</a>';
        }
        $html .= '</div>';
        return $html;
    }
}
