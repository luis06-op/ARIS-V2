<?php

namespace App\Services\Storage;

use App\Models\DocumentVersion;
use App\Models\FileBlob;
use Illuminate\Support\Facades\Storage;

final class EvidenceIntegrity
{
    public function matchesMetadata(DocumentVersion $version, FileBlob $blob): bool
    {
        return $version->blob_id === $blob->id
            && (int) $version->size_bytes === (int) $blob->size_bytes
            && hash_equals($version->sha256, $blob->sha256)
            && $version->mime === $blob->mime && $version->extension === $blob->extension;
    }

    /** @return resource|false Copia temporal verificada; quien la recibe debe cerrarla. */
    public function openVersion(DocumentVersion $version): mixed
    {
        $blob = $version->blob()->first();

        return $blob && $this->matchesMetadata($version, $blob) ? $this->openBlob($blob) : false;
    }

    /** @return resource|false */
    public function openBlob(FileBlob $blob): mixed
    {
        if ($blob->status !== 'disponible' || $blob->disk !== 'local'
            || ! preg_match('~\Adocuments/[a-zA-Z0-9_-]+\.[a-zA-Z0-9]+\z~', $blob->storage_key)) {
            return false;
        }

        $disk = Storage::disk('local');
        $root = realpath($disk->path(''));
        $path = realpath($disk->path($blob->storage_key));
        if (! $root || ! $path || ! str_starts_with($path, $root.DIRECTORY_SEPARATOR)
            || is_link($disk->path($blob->storage_key)) || ! is_file($path)) {
            return false;
        }

        $source = @fopen($path, 'rb');
        if ($source === false) {
            return false;
        }

        $snapshot = false;
        try {
            $size = (int) $blob->size_bytes;
            $stat = fstat($source);
            if ($size <= 0 || $size > (int) config('aris.files.max_bytes') || ! $stat || $stat['size'] !== $size) {
                return false;
            }
            $snapshot = fopen('php://temp/maxmemory:2097152', 'w+b');
            if ($snapshot === false || stream_copy_to_stream($source, $snapshot, $size + 1) !== $size) {
                return false;
            }
            rewind($snapshot);
            $hash = hash_init('sha256');
            hash_update_stream($hash, $snapshot);
            if (! hash_equals($blob->sha256, hash_final($hash))) {
                return false;
            }
            rewind($snapshot);
            $verified = $snapshot;
            $snapshot = false;

            return $verified;
        } finally {
            fclose($source);
            if (is_resource($snapshot)) {
                fclose($snapshot);
            }
        }
    }
}
