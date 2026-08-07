<?php

class PlatformTranslationService
{
    private static bool $loaded = false;
    private static ?object $translator = null;

    public static function available(): bool
    {
        return self::translator() !== null;
    }

    public static function translate_list(array $texts, string $from, string $to): array
    {
        $translator = self::translator();
        if ($translator === null) return $texts;
        try {
            $translator->setSource($from);
            $translator->setTarget($to);
            return $translator->translate($texts);
        } catch (\Throwable $error) {
            return $texts;
        }
    }

    public static function translate(string $text, string $from, string $to): string
    {
        $result = self::translate_list([$text], $from, $to);
        return $result[0] ?? $text;
    }

    private static function translator(): ?object
    {
        if (self::$loaded) return self::$translator;
        self::$loaded = true;

        $root = defined('ENGINE_PROJECT_ROOT') ? ENGINE_PROJECT_ROOT : dirname(__DIR__, 2);
        $autoload = $root . '/dev/dependencies/google-translate-php/vendor/autoload.php';

        if (!file_exists($autoload)) return null;

        require_once $autoload;
        if (class_exists('Stichoza\GoogleTranslate\GoogleTranslate')) {
            self::$translator = new \Stichoza\GoogleTranslate\GoogleTranslate();
        }

        return self::$translator;
    }
}
