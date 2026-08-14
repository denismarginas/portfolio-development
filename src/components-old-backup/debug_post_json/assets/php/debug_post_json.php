<?php

class DebugPostJson
{
    public static function render(array $data): string
    {
        $postData = $data['post_current_data'] ?? $data;
        $json = json_encode($postData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return '<pre style="background:#111;color:#0f0;padding:24px;border-radius:0px;font-size:13px;line-height:1.5;overflow:auto;max-height:80vh;white-space:pre-wrap;word-break:break-all;margin:0;">' . htmlspecialchars($json) . '</pre>';
    }
}
