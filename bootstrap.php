<?php

declare(strict_types=1);

session_start();

function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, ""'");

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

load_env(__DIR__ . '/.env');

$config = require __DIR__ . '/config/app.php';

if (isset($_GET['lang']) && in_array($_GET['lang'], $config['supported_languages'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

$langCode = $_SESSION['lang'] ?? $config['default_language'];

if (!in_array($langCode, $config['supported_languages'], true)) {
    $langCode = $config['default_language'];
}

$lang = require __DIR__ . '/lang/' . $langCode . '.php';

function t(string $key, ?string $fallback = null): string
{
    global $lang;
    return $lang[$key] ?? $fallback ?? $key;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

require_once __DIR__ . '/config/database.php';
