<?php

require_once __DIR__ . '/../../../../../dev/engine/bootstrap.php';

class ThemeCompileCss
{
    public static function compile(?string $filterComponent = null): array
    {
        $themeDir = EngineConfig::get('theme_dir');
        $themeAssetsDir = $themeDir . '/assets';
        $scssDir = EngineConfig::get('theme_scss_dir');
        $cssDir = EngineConfig::get('theme_css_dir');
        $componentsDir = EngineConfig::get('components_dir');

        $configPath = $themeDir . '/component.json';
        $themeConfig = json_decode(file_get_contents($configPath), true);
        $compileOrder = $themeConfig['compile_order'] ?? [];
        $outputFile = $themeConfig['output']['css'] ?? 'style.css';
        $minify = $themeConfig['minify'] ?? false;

        $loadPaths = [];
        foreach ($compileOrder as $relPath) {
            $absPath = $themeAssetsDir . '/' . $relPath;
            if (is_dir($absPath)) {
                $loadPaths[] = $absPath;
            }
        }
        if (!in_array($scssDir, $loadPaths, true)) {
            array_unshift($loadPaths, $scssDir);
        }

        $cssOutput = '';

        $styleFile = $scssDir . '/style.scss';
        if (file_exists($styleFile)) {
            try {
                $compiler = new ScssCompiler();
                foreach ($loadPaths as $lp) {
                    $compiler->add_load_path($lp);
                }
                $cssOutput .= $compiler->compile_file($styleFile) . "\n";
            } catch (\Exception $e) {
                $cssOutput .= '/* Error compiling theme: ' . $e->getMessage() . ' */' . "\n";
            }
        }

        $components = DataService::get_all_component_names();
        foreach ($components as $componentName) {
            if ($componentName === 'theme') continue;
            if ($filterComponent !== null && $filterComponent !== $componentName) continue;

            $scssFile = $componentsDir . '/' . $componentName . '/assets/scss/style.scss';
            if (!file_exists($scssFile)) continue;

            try {
                $compiler = new ScssCompiler();
                foreach ($loadPaths as $lp) {
                    $compiler->add_load_path($lp);
                }
                $compiler->add_load_path(dirname($scssFile));
                $cssOutput .= $compiler->compile_file($scssFile) . "\n";
            } catch (\Exception $e) {
                $cssOutput .= '/* Error compiling ' . $componentName . ': ' . $e->getMessage() . ' */' . "\n";
            }
        }

        if (!is_dir($cssDir)) {
            mkdir($cssDir, 0755, true);
        }
        $outputPath = $cssDir . '/' . $outputFile;
        file_put_contents($outputPath, $cssOutput);

        $result = ['output' => $outputPath, 'size' => strlen($cssOutput), 'status' => 'compiled'];

        if ($minify) {
            $compiler = new ScssCompiler();
            $minified = $compiler->minify($cssOutput);
            $minPath = $cssDir . '/' . preg_replace('/\.css$/', '.min.css', $outputFile);
            file_put_contents($minPath, $minified);
            $result['min_size'] = strlen($minified);
            $result['min_output'] = $minPath;
        }

        return $result;
    }
}

if (php_sapi_name() === 'cli' && !defined('IN_THEME_COMPILE_CSS')) {
    $filter = $argv[1] ?? null;
    $result = ThemeCompileCss::compile($filter);
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
}
