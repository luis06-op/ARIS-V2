<?php

namespace Tests\Feature\Database;

use App\Models\FileBlob;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationalIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_blob_hash_cannot_be_replaced_but_quarantine_status_can_change(): void
    {
        $blob = FileBlob::create(['uuid' => (string) Str::uuid(), 'disk' => 'local', 'storage_key' => 'documents/prueba.pdf',
            'size_bytes' => 10, 'sha256' => str_repeat('a', 64), 'mime' => 'application/pdf', 'extension' => 'pdf', 'status' => 'disponible']);
        $blob->update(['status' => 'cuarentena']);
        $this->assertSame('cuarentena', $blob->fresh()->status);
        $this->expectException(QueryException::class);
        $blob->update(['sha256' => str_repeat('b', 64)]);
    }

    public function test_critical_indexes_and_restrict_foreign_keys_exist(): void
    {
        foreach ([
            'submissions' => ['project_id', 'status'],
            'submission_versions' => ['submission_id', 'version_number'],
            'file_blobs' => ['sha256'],
            'audit_events' => ['institution_id', 'occurred_at'],
        ] as $table => $columns) {
            $this->assertTrue(collect(Schema::getIndexes($table))->contains(fn ($index) => $index['columns'] === $columns), $table);
        }
        foreach (['submissions', 'submission_versions', 'document_versions', 'audit_events'] as $table) {
            foreach (Schema::getForeignKeys($table) as $key) {
                $this->assertContains(strtolower($key['on_delete']), ['restrict', 'no action'], $table);
            }
        }
    }
}
