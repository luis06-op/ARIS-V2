<?php

use App\Actions\Identity\ConfigureEvaluatorScope;
use App\Actions\Projects\ManageProjectTutor;
use App\Models\Institution;
use App\Models\ProjectMembership;
use App\Models\Submission;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Tests\Support\AcademicScenario;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$database = config('database.connections.sqlite.database');
if (! $app->environment('testing') || config('database.default') !== 'sqlite'
    || ! str_starts_with(basename(dirname($database)), 'aris-browser-')
    || ! is_file(dirname($database).'/.synthetic') || filesize($database) !== 0) {
    throw new RuntimeException('El escenario requiere una base SQLite nueva y sintética del ejecutor de navegador.');
}
Artisan::call('migrate', ['--force' => true]);
$scenario = new class
{
    use AcademicScenario;

    public function create(): array
    {
        $institution = Institution::create(['name' => 'Institución sintética de QA']);
        $users = [];
        foreach (['administrador', 'instructor', 'evaluador', 'aprendiz'] as $role) {
            $users[$role] = $this->member($institution, $role);
            $users[$role]->update(['email' => $role.'@example.invalid', 'password' => getenv('ARIS_BROWSER_PASSWORD')]);
        }
        $ficha = $this->ficha($institution);
        $project = $this->project($ficha, $users['administrador']);
        app(ManageProjectTutor::class)->execute($users['administrador'], $project, $users['instructor'], true);
        app(ConfigureEvaluatorScope::class)->execute($users['administrador'], $users['evaluador']->institutionMemberships()->firstOrFail(), $ficha, true);
        ProjectMembership::create(['project_id' => $project->id, 'user_id' => $users['aprendiz']->id, 'function' => 'aprendiz', 'assigned_at' => today()]);
        $submission = Submission::create(['project_id' => $project->id, 'author_id' => $users['aprendiz']->id, 'title' => 'Entrega sintética de QA']);
        $version = $submission->versions()->create(['version_number' => 1, 'author_id' => $users['aprendiz']->id, 'summary' => 'Borrador sintético para validar la interfaz']);

        return ['version' => $version->id, 'project' => $project->id, 'institution' => $institution->id];
    }
};
echo json_encode($scenario->create(), JSON_THROW_ON_ERROR);
