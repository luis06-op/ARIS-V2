<?php

namespace Tests\Feature\Audit;

use App\Models\Institution;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

/**
 * P2.1: el visor de auditoría debe aceptar campos opcionales vacíos sin debilitar la
 * validación de los valores que sí se envían.
 */
class AuditOptionalFiltersTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    private function urlConFiltros(array $filtros): string
    {
        return '/auditoria?'.http_build_query($filtros);
    }

    public function test_filtros_opcionales_vacios_son_aceptados(): void
    {
        $institution = Institution::create(['name' => 'Institución', 'status' => 'activa']);
        $admin = $this->member($institution, 'administrador');

        $this->actingAs($admin)->get($this->urlConFiltros([
            'accion' => '', 'actor_id' => '', 'desde' => '', 'hasta' => '',
        ]))->assertOk();

        $this->actingAs($admin)->get($this->urlConFiltros(['accion' => '']))->assertOk();
        $this->actingAs($admin)->get($this->urlConFiltros(['actor_id' => '']))->assertOk();
        $this->actingAs($admin)->get($this->urlConFiltros(['desde' => '', 'hasta' => '']))->assertOk();
    }
}
