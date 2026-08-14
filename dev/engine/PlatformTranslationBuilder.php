<?php

class PlatformTranslationBuilder
{
    private static array $skipKeys = ['_id','slug','id','img','src','url','path','file','component','variant','filename','iso','post_type','extension','index','keywords','icon','menu_name'];

    public static function language_config(): array
    {
        $languages = PlatformDataService::get_data('settings_languages') ?? [];
        $list = $languages['list'] ?? [];
        $default = $languages['default'] ?? ($list[0]['iso'] ?? 'en');
        $targets = [];

        $translation = PlatformWorkflowService::section('translation');
        $defaultValue = (string) ($translation['default_lang'] ?? '');
        if ($defaultValue !== '') $default = $defaultValue;
        $targetValue = (string) ($translation['target_langs'] ?? '');
        if ($targetValue !== '') {
            $targets = array_values(array_filter(array_map('trim', explode(',', $targetValue))));
        }

        if ($targets !== []) {
            $knownIso = [];
            foreach ($list as $lang) $knownIso[$lang['iso'] ?? ''] = $lang;
            $filtered = [];
            foreach ($targets as $iso) {
                if (isset($knownIso[$iso])) $filtered[] = $knownIso[$iso];
                else $filtered[] = ['iso' => $iso, 'text' => $iso];
            }
            $list = $filtered;
        }

        return ['default' => $default, 'list' => $list];
    }
    public static function run(): array
    {
        $config = self::language_config();
        $dataDir = PlatformConfig::get('data_dir');
        $sourceDir = is_dir($dataDir . '/' . $config['default']) ? $dataDir . '/' . $config['default'] : $dataDir;
        $files = self::glob_recursive($sourceDir, 'data_*.json');
        $total = 0; $success = 0; $results = [];

        foreach ($config['list'] as $lang) {
            $iso = $lang['iso'] ?? '';
            if ($iso === '' || $iso === $config['default']) continue;
            foreach ($files as $file) {
                $name = basename($file);
                if (str_starts_with($name, 'data_settings_')) continue;
                $rel = trim(substr($file, strlen($sourceDir)), '/\\');
                $total++;
                $data = self::translate_file($file, $config['default'], $iso);
                if ($data === null) { $results[] = $iso . '/' . $rel . ' (invalid)'; continue; }
                $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $relDir = dirname($rel);
                $targetDir = $dataDir . '/' . $iso . ($relDir === '.' ? '' : '/' . $relDir);
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                if (file_put_contents($targetDir . '/' . $name, $json, LOCK_EX) !== false) {
                    $success++;
                    $results[] = $iso . '/' . $rel;
                }
            }
        }

        return ['default' => $config['default'], 'languages' => $config['list'], 'total' => $total, 'success_count' => $success, 'error_count' => $total - $success, 'results' => $results];
    }

    private static function glob_recursive(string $dir, string $pattern): array
    {
        if (!is_dir($dir)) return [];
        $files = glob($dir . '/' . $pattern);
        foreach (glob($dir . '/*', GLOB_ONLYDIR) as $subDir) {
            $files = array_merge($files, self::glob_recursive($subDir, $pattern));
        }
        return $files ?: [];
    }

    private static function translate_file(string $path, string $from, string $to): ?array
    {
        $content = file_get_contents($path);
        if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) $content = substr($content, 3);
        $data = json_decode($content, true);
        if (!is_array($data)) return null;

        $strings = [];
        self::collect_strings($data, $strings);
        if (!$strings) return $data;

        $translated = PlatformTranslationService::translate_list(array_values($strings), $from, $to);
        return count($translated) === count($strings) ? self::apply_strings($data, array_combine(array_keys($strings), $translated)) : $data;
    }

    private static function collect_strings(array $data, array &$strings, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            if (in_array($key, self::$skipKeys, true)) continue;
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) self::collect_strings($value, $strings, $path);
            elseif (is_string($value) && self::is_translatable($value)) $strings[$path] = $value;
        }
    }

    private static function is_translatable(string $value): bool
    {
        if ($value === '' || is_numeric($value)) return false;
        if (preg_match('/^[\w\-.]+@[\w\-.]+$/', $value)) return false;
        if (preg_match('~^(https?:)?//~i', $value)) return false;
        if (str_contains($value, '/')) return false;
        if (preg_match('/\.(webp|png|jpg|jpeg|gif|svg|ico|json|html|css|js|txt|pdf|mp4|webm|mp3|woff2?|ttf|otf)$/i', $value)) return false;
        return !preg_match('/^[\w\-.]+$/i', $value);
    }

    private static function apply_strings(array $data, array $map, string $prefix = ''): array
    {
        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) $data[$key] = self::apply_strings($value, $map, $path);
            elseif (array_key_exists($path, $map)) $data[$key] = $map[$path];
        }
        return $data;
    }
}
