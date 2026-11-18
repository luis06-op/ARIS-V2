<?php

use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

// La suite borra tablas: exigir una base de pruebas explícita y un servidor identificado.
$database = getenv('DB_DATABASE') ?: '';
$socket = getenv('DB_SOCKET') ?: '';
if (getenv('ARIS_MYSQL_DISPOSABLE') !== '1' || ! preg_match('/\Aaris_test_[a-z0-9_]+\z/', $database) || $socket === '') {
    fwrite(STDERR, "Se requiere ARIS_MYSQL_DISPOSABLE=1, DB_SOCKET explícito y DB_DATABASE=aris_test_*.\n");
    exit(1);
}
$pdo = new PDO('mysql:unix_socket='.$socket.';dbname='.$database, getenv('DB_USERNAME') ?: '', getenv('DB_PASSWORD') ?: '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$identity = $pdo->query('SELECT VERSION() AS version, DATABASE() AS base, @@socket AS socket, @@datadir AS directorio')->fetch(PDO::FETCH_ASSOC);
if (! str_starts_with($identity['version'], '8.') || str_contains($identity['version'], 'MariaDB') || realpath($identity['socket']) !== realpath($socket)) {
    throw new RuntimeException('El servidor no coincide con el MySQL 8.x aislado solicitado.');
}
echo json_encode($identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
$process = new Process([PHP_BINARY, 'artisan', 'test'], dirname(__DIR__), [
    'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => $database,
    'DB_SOCKET' => $socket, 'DB_URL' => '', 'MAIL_MAILER' => 'array',
    'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync', 'SESSION_DRIVER' => 'array',
]);
$process->setTimeout(900);
exit($process->run(fn ($type, $output) => print ($output)));
