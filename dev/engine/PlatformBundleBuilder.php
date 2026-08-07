<?php

class PlatformBundleBuilder
{
    public static function build(): array
    {
        $componentsDir = PlatformConfig::get('components_dir');
        $compiledDir = PlatformConfig::get('theme_compiled_dir');
        $compiledPath = PlatformConfig::get('theme_compiled_path');

        $componentNames = array_map('basename', glob($componentsDir . '/*', GLOB_ONLYDIR));
        sort($componentNames);
        $order = array_merge(['theme'], array_values(array_filter($componentNames, fn($n) => $n !== 'theme')));

        $cssContent = '';
        $jsContent = '';

        foreach ($order as $name) {
            $configPath = $componentsDir . '/' . $name . '/component.json';
            if (!file_exists($configPath)) continue;
            $config = json_decode(file_get_contents($configPath), true);
            if (!$config) continue;

            $assets = $config['assets'] ?? [];
            foreach ($assets['css'] ?? [] as $file) {
                if (str_ends_with($file, '.scss')) continue;
                $fullPath = $componentsDir . '/' . $name . '/' . $file;
                if (file_exists($fullPath)) {
                    $cssContent .= file_get_contents($fullPath) . "\n";
                }
            }
            foreach ($assets['js'] ?? [] as $file) {
                $fullPath = $componentsDir . '/' . $name . '/' . $file;
                if (file_exists($fullPath)) {
                    $jsContent .= file_get_contents($fullPath) . "\n";
                }
            }
        }

        $cssDir = $compiledDir . '/css';
        $jsDir = $compiledDir . '/js';
        if (!is_dir($cssDir)) mkdir($cssDir, 0755, true);
        if (!is_dir($jsDir)) mkdir($jsDir, 0755, true);

        file_put_contents($cssDir . '/bundle.css', $cssContent);
        file_put_contents($jsDir . '/bundle.js', $jsContent);

        return [
            [
                'file' => $compiledPath . '/css/bundle.css',
                'success' => true,
                'bytes' => strlen($cssContent),
                'message' => 'Written',
            ],
            [
                'file' => $compiledPath . '/js/bundle.js',
                'success' => true,
                'bytes' => strlen($jsContent),
                'message' => 'Written',
            ],
        ];
    }
}
