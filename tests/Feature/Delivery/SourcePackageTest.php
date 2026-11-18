<?php

namespace Tests\Feature\Delivery;

use App\Services\Delivery\SourcePackageBuilder;
use App\Services\Delivery\SourcePackageLayout;
use App\Services\Delivery\SourcePackageVerifier;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;
use ZipArchive;

class SourcePackageTest extends TestCase
{
    private string $directory;

    private string $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/package-'.bin2hex(random_bytes(8)));
        $this->repository = $this->directory.'/repository';
        File::makeDirectory($this->repository, 0755, true);
        foreach ([...SourcePackageLayout::REQUIRED, 'app/Example.php', '.env', 'database/example.sqlite', 'tests/fixtures/example.pdf', 'vendor/example.php', 'public/build/example.js'] as $path) {
            File::ensureDirectoryExists(dirname($this->repository.'/'.$path));
            file_put_contents($this->repository.'/'.$path, 'Contenido sintético: '.$path);
        }
        $this->git(['init']);
        $this->git(['add', '.']);
        $this->git(['-c', 'user.name=Pruebas', '-c', 'user.email=pruebas@example.invalid', 'commit', '-m', 'Fuentes sintéticas']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    public function test_packages_are_reproducible_and_exclude_data_dependencies_and_fixtures(): void
    {
        $manifest = $this->build('first.zip');
        $this->build('second.zip');
        $this->assertSame(hash_file('sha256', $this->directory.'/first.zip'), hash_file('sha256', $this->directory.'/second.zip'));
        $this->assertSame(count(SourcePackageLayout::REQUIRED) + 1, count($manifest['archivos']));
        $this->assertSame($manifest, app(SourcePackageVerifier::class)->verify($this->directory.'/first.zip'));
        $this->artisan('aris:verificar-paquete', ['archivo' => $this->directory.'/first.zip'])->assertSuccessful();
    }

    public function test_dirty_repository_is_rejected(): void
    {
        file_put_contents($this->repository.'/artisan', 'Modificado');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('limpio');
        $this->build('dirty.zip');
    }

    public function test_existing_destination_is_preserved(): void
    {
        file_put_contents($this->directory.'/existing.zip', 'Conservar');
        try {
            $this->build('existing.zip');
            $this->fail('Debió rechazar el destino existente.');
        } catch (RuntimeException) {
            $this->assertSame('Conservar', file_get_contents($this->directory.'/existing.zip'));
        }
    }

    public function test_tracked_symlink_is_rejected(): void
    {
        symlink('Example.php', $this->repository.'/app/Link.php');
        $this->git(['add', '.']);
        $this->git(['-c', 'user.name=Pruebas', '-c', 'user.email=pruebas@example.invalid', 'commit', '-m', 'Enlace sintético']);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('simbólicos');
        $this->build('link.zip');
    }

    #[DataProvider('mutations')]
    public function test_modified_package_is_rejected(string $mutation): void
    {
        $manifest = $this->build('modified.zip');
        $path = $this->directory.'/modified.zip';
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        if ($mutation === 'bytes') {
            $zip->addFromString('artisan', str_repeat('x', strlen('Contenido sintético: artisan')));
        } elseif ($mutation === 'missing') {
            $zip->deleteName('artisan');
        } elseif ($mutation === 'extra') {
            $zip->addFromString('.env', 'Secreto sintético');
        } elseif ($mutation === 'symlink') {
            $zip->setExternalAttributesName('artisan', ZipArchive::OPSYS_UNIX, 0120777 << 16);
        } elseif ($mutation === 'json') {
            $zip->addFromString(SourcePackageLayout::MANIFEST, '{');
        } else {
            if ($mutation === 'traversal') {
                $manifest['archivos'][0]['ruta'] = '../artisan';
            } elseif ($mutation === 'duplicate') {
                $manifest['archivos'][] = $manifest['archivos'][0];
            } elseif ($mutation === 'limit') {
                $manifest['archivos'][0]['bytes'] = SourcePackageLayout::MAX_FILE_BYTES + 1;
            } else {
                $manifest['archivos'] = 'incorrecto';
            }
            $zip->addFromString(SourcePackageLayout::MANIFEST, json_encode($manifest));
        }
        $zip->close();
        $this->expectException(RuntimeException::class);
        app(SourcePackageVerifier::class)->verify($path);
    }

    public static function mutations(): array
    {
        return array_map(fn ($value) => [$value], ['bytes', 'missing', 'extra', 'symlink', 'json', 'traversal', 'duplicate', 'limit', 'structure']);
    }

    private function build(string $name): array
    {
        return app(SourcePackageBuilder::class)->build($this->repository, $this->directory.'/'.$name);
    }

    private function git(array $arguments): void
    {
        (new Process(['git', ...$arguments], $this->repository))->mustRun();
    }
}
