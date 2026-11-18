<?php

namespace App\Services\Delivery;

final class SourcePackageLayout
{
    public const MANIFEST = 'MANIFIESTO.json';

    public const MAX_FILES = 10000;

    public const MAX_FILE_BYTES = 64 * 1024 * 1024;

    public const MAX_TOTAL_BYTES = 256 * 1024 * 1024;

    public const REQUIRED = ['artisan', 'composer.json', 'composer.lock', '.env.example', 'bootstrap/app.php', 'bootstrap/cache/.gitignore', 'public/index.php', 'AVISO-DE-DERECHOS.md'];

    public static function allows(string $path): bool
    {
        if ($path === '' || str_contains($path, '\\') || preg_match('/[\x00-\x1f:]/', $path)
            || str_starts_with($path, '/') || array_intersect(explode('/', $path), ['', '.', '..'])) {
            return false;
        }
        if (in_array($path, [
            ...self::REQUIRED, 'README.md', 'package.json', 'package-lock.json', 'vite.config.js', 'setup_admin.php',
            'public/.htaccess', 'public/favicon.svg', 'public/favicon.ico', 'public/robots.txt',
        ], true)) {
            return true;
        }
        if (str_starts_with($path, 'storage/')) {
            return str_ends_with($path, '/.gitignore');
        }
        if (str_starts_with($path, 'bootstrap/cache/')) {
            return false;
        }
        foreach (explode('/', $path) as $segment) {
            if (str_starts_with($segment, '.')) {
                return false;
            }
        }
        if (str_starts_with($path, 'config/') && str_contains(basename($path), 'local')) {
            return false;
        }
        foreach (['app/', 'bootstrap/', 'config/', 'routes/', 'lang/', 'database/migrations/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return str_ends_with($path, '.php');
            }
        }
        if (str_starts_with($path, 'resources/')) {
            return in_array(pathinfo($path, PATHINFO_EXTENSION), ['php', 'css', 'js', 'json', 'svg', 'woff', 'woff2', 'ttf'], true);
        }
        if (str_starts_with($path, 'public/assets/brand/')) {
            return str_ends_with($path, '.svg');
        }

        return (str_starts_with($path, 'docs/operations/') || str_starts_with($path, 'docs/deployment/'))
            && str_ends_with($path, '.md');
    }
}
