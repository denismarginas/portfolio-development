<?php

class SectionDebug
{
    public static function render(array $data): string
    {
        if (!defined('DEBUG') || DEBUG !== true) {
            return '';
        }

        $renderUrl = $GLOBALS['urlPath'] . 'content/render/render.php';

        $logEntries = '';
        global $log;
        if (!empty($log)) {
            foreach ($log as $entry) {
                $logEntries .= '<li>' . $entry . '</li>';
            }
        }

        $template = file_get_contents(__DIR__ . '/../html/template.html');
        return str_replace(
            ['{{ render_url }}', '{{ log_entries }}'],
            [$renderUrl, $logEntries],
            $template
        );
    }
}
