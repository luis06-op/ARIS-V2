<?php

namespace Tests\Feature\Delivery;

use App\Services\Delivery\BackupArchive;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class BackupArchiveTest extends TestCase
{
    private BackupArchive $archive;

    protected function setUp(): void
    {
        parent::setUp();
        $this->archive = app(BackupArchive::class);
    }

    public function test_respaldo_valido_se_verifica_con_su_hash(): void
    {
        $path = $this->archive->build(['database.sql' => "CREATE TABLE x (id int);\n", 'private/documents/a.pdf' => '%PDF-1.4 contenido']);
        $hash = hash_file('sha256', $path);
        $manifest = $this->archive->verify($path, $hash);
        $this->assertSame('respaldo-mysql-privado', $manifest['tipo']);
        $this->assertCount(2, $manifest['archivos']);
        unlink($path);
    }

    public function test_hash_de_referencia_incorrecto_se_rechaza(): void
    {
        $path = $this->archive->build(['database.sql' => 'contenido']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SHA-256');
        try {
            $this->archive->verify($path, str_repeat('a', 64));
        } finally {
            unlink($path);
        }
    }

    public function test_hash_debe_ser_confiable_y_no_opcional(): void
    {
        $path = $this->archive->build(['database.sql' => 'contenido']);
        $this->expectException(RuntimeException::class);
        try {
            $this->archive->verify($path, '');
        } finally {
            unlink($path);
        }
    }

    public function test_falta_el_volcado_de_base(): void
    {
        $path = $this->archive->build(['private/documents/a.pdf' => 'pdf']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('incompleto');
        try {
            $this->archive->verify($path, hash_file('sha256', $path));
        } finally {
            unlink($path);
        }
    }

    public function test_archivo_extra_no_inventariado_se_rechaza(): void
    {
        $path = $this->archive->build(['database.sql' => 'sql']);
        $zip = new ZipArchive;
        $zip->open($path);
        $zip->addFromString('.env', 'secreto');
        $zip->close();
        $this->expectException(RuntimeException::class);
        try {
            $this->archive->verify($path, hash_file('sha256', $path));
        } finally {
            unlink($path);
        }
    }

    public function test_contenido_modificado_respeto_al_inventario_se_rechaza(): void
    {
        $path = $this->archive->build(['database.sql' => 'original']);
        $zip = new ZipArchive;
        $zip->open($path);
        $zip->addFromString('database.sql', 'sustituido');
        $zip->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('inventario');
        try {
            $this->archive->verify($path, hash_file('sha256', $path));
        } finally {
            unlink($path);
        }
    }

    public function test_rutas_no_permitidas_se_rechazan(): void
    {
        foreach (['../ fuera', 'private/../secreto', '/absoluto', 'private/a\\b'] as $bad) {
            $path = $this->archive->build(['database.sql' => 'sql', $bad => 'x']);
            try {
                $this->expectException(RuntimeException::class);
                $this->archive->verify($path, hash_file('sha256', $path));
            } finally {
                unlink($path);
            }
        }
    }

    public function test_lista_de_rutas_permitidas(): void
    {
        $this->assertTrue($this->archive->allowed('private/documents/a.pdf'));
        $this->assertTrue($this->archive->allowed('database.sql'));
        $this->assertFalse($this->archive->allowed('.env'));
        $this->assertFalse($this->archive->allowed('private/../secreto'));
    }

    public function test_entrada_no_regular_se_rechaza(): void
    {
        $path = $this->archive->build(['database.sql' => 'sql']);
        $zip = new ZipArchive;
        $zip->open($path);
        $zip->setExternalAttributesName('database.sql', ZipArchive::OPSYS_UNIX, 0040755 << 16);
        $zip->close();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('regulares');
        try {
            $this->archive->verify($path, hash_file('sha256', $path));
        } finally {
            unlink($path);
        }
    }
}
