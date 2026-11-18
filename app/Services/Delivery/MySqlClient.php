<?php

namespace App\Services\Delivery;

use RuntimeException;
use Symfony\Component\Process\Process;

final class MySqlClient
{
    public function run(string $binary, array $arguments, array $connection, ?string $input = null): void
    {
        $file = tempnam(storage_path('framework'), 'mysql-options-');
        if ($file === false) {
            throw new RuntimeException('No se pudo preparar la configuración privada del cliente.');
        }
        chmod($file, 0600);
        try {
            $options = ['user' => $connection['username'], 'password' => $connection['password'] ?? ''];
            if (! empty($connection['unix_socket'])) {
                $options['socket'] = $connection['unix_socket'];
            } else {
                $options['host'] = $connection['host'];
                $options['port'] = $connection['port'];
            }
            $text = "[client]\n";
            foreach ($options as $key => $value) {
                $text .= $key.'="'.str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], (string) $value)."\"\n";
            }
            file_put_contents($file, $text);
            $process = new Process([$binary, '--defaults-file='.$file, ...$arguments]);
            $process->setTimeout(600);
            $stream = $input === null ? null : fopen($input, 'rb');
            try {
                if ($stream !== null) {
                    $process->setInput($stream);
                }
                $process->mustRun();
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        } finally {
            unlink($file);
        }
    }
}
