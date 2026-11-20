<?php

namespace Tests\Feature\Submissions;

use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

/**
 * P2.1: el buscador de entregas debe aceptar campos opcionales vacíos tal como los envían
 * los formularios HTML reales, sin debilitar la validación de valores no vacíos.
 */
class SearchOptionalFiltersTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    private function urlConFiltros(array $filtros): string
    {
        return '/entregas/buscar?'.http_build_query($filtros);
    }

    public function test_todos_los_campos_opcionales_vacios_devuelven_200(): void
    {
        $institution = Institution::create(['name' => 'Institución', 'status' => 'activa']);
        $user = $this->member($institution, 'aprendiz');

        $this->actingAs($user)->get($this->urlConFiltros([
            'q' => '', 'estado' => '', 'ficha_id' => '', 'desde' => '', 'hasta' => '',
        ]))->assertOk();
    }

    public function test_cada_campo_opcional_vacio_por_separado_es_aceptado(): void
    {
        $institution = Institution::create(['name' => 'Institución', 'status' => 'activa']);
        $user = $this->member($institution, 'aprendiz');

        foreach ([
            ['estado' => ''],
            ['ficha_id' => ''],
            ['desde' => ''],
            ['hasta' => ''],
            ['q' => ''],
        ] as $filtro) {
            $this->actingAs($user)->get($this->urlConFiltros($filtro))->assertOk();
        }
    }

    public function test_filtro_por_ficha_combinado_con_campos_vacios_sigue_funcionando(): void
    {
        $institution = Institution::create(['name' => 'Institución', 'status' => 'activa']);
        $user = $this->member($institution, 'aprendiz');
        $ficha = $this->ficha($institution);

        $this->actingAs($user)->get($this->urlConFiltros([
            'q' => '', 'estado' => '', 'ficha_id' => $ficha->id, 'desde' => '', 'hasta' => '',
        ]))->assertOk();
    }
}
