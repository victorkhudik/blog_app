<?php
require_once __DIR__ . '/../vendor/autoload.php';
class ConfigLoader
{
    public static function loadEnv($path = __DIR__ . '/../.env')
    {
        if (!file_exists($path)) {
            throw new \Exception(".env file not found at: " . $path);
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                $value = trim($value, '"\'');

                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;

                putenv("{$key}={$value}");
            }
        }
    }
}