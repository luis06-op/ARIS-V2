<?php

use App\Http\Controllers\Academic\CatalogController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Guides\GuideController;
use App\Http\Controllers\Identity\AccessController;
use App\Http\Controllers\Identity\MemberController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\Projects\LearnerController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Projects\TutorController;
use App\Http\Controllers\Submissions\EvidenceController;
use App\Http\Controllers\Submissions\SubmissionController;
use App\Http\Controllers\Submissions\UnlinkedSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', InicioController::class)->name('inicio');

Route::middleware('guest')->group(function (): void {
    Route::get('/ingresar', [SessionController::class, 'create'])->name('sesion.create');
    Route::post('/ingresar', [SessionController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('sesion.store');

    Route::get('/registro', [RegistrationController::class, 'create'])->name('registro.create');
    Route::post('/registro', [RegistrationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('registro.store');

    Route::get('/olvide-password', [PasswordResetController::class, 'create'])
        ->name('clave.olvidada');
    Route::post('/olvide-password', [PasswordResetController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('clave.enviar');
    Route::get('/restablecer-password/{token}', [PasswordResetController::class, 'createReset'])
        ->name('clave.restablecer');
    Route::post('/restablecer-password', [PasswordResetController::class, 'storeReset'])
        ->middleware('throttle:5,1')
        ->name('clave.actualizar');
});

Route::post('/salir', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('sesion.destroy');

Route::get('/cuenta', [AccountController::class, 'show'])
    ->middleware('auth')
    ->name('cuenta');

Route::middleware('auth')->group(function (): void {
    Route::post('/proyectos/{project}/aprendiz', LearnerController::class)->name('proyectos.aprendiz');
    Route::post('/accesos/{institution}/miembros', MemberController::class)->name('miembros.store');
    Route::post('/accesos/{institution}/catalogo', CatalogController::class)->name('catalogo.store');
    Route::post('/proyectos/{project}/entregas/excepcion', UnlinkedSubmissionController::class)->name('entregas.excepcion');
    Route::post('/proyectos/{project}/entregas', [SubmissionController::class, 'store'])->name('entregas.store');
    Route::get('/entregas/versiones/{version}', [SubmissionController::class, 'show'])->name('entregas.show');
    Route::post('/entregas/versiones/{version}/versiones', [SubmissionController::class, 'version'])->name('entregas.version');
    Route::post('/entregas/versiones/{version}/evidencias', [EvidenceController::class, 'store'])->middleware('throttle:20,1')->name('evidencias.store');
    Route::get('/evidencias/versiones/{documentVersion}/descargar', [EvidenceController::class, 'download'])->name('evidencias.download');
    Route::post('/proyectos/{project}/guias', [GuideController::class, 'store'])->name('guias.store');
    Route::get('/guias/versiones/{version}', [GuideController::class, 'show'])->name('guias.show');
    Route::patch('/guias/versiones/{version}', [GuideController::class, 'update'])->name('guias.update');
    Route::post('/guias/versiones/{version}/publicar', [GuideController::class, 'publish'])->name('guias.publish');
    Route::get('/accesos', [AccessController::class, 'index'])->name('accesos.index');
    Route::get('/accesos/{institution}', [AccessController::class, 'show'])->name('accesos.show');
    Route::post('/accesos/{institution}', [AccessController::class, 'store'])->name('accesos.store');
    Route::get('/proyectos', [ProjectController::class, 'index'])->name('proyectos.index');
    Route::get('/proyectos/crear', [ProjectController::class, 'create'])->name('proyectos.create');
    Route::post('/proyectos', [ProjectController::class, 'store'])->name('proyectos.store');
    Route::get('/proyectos/{project}', [ProjectController::class, 'show'])->name('proyectos.show');
    Route::get('/proyectos/{project}/editar', [ProjectController::class, 'edit'])->name('proyectos.edit');
    Route::patch('/proyectos/{project}', [ProjectController::class, 'update'])->name('proyectos.update');
    Route::post('/proyectos/{project}/tutor', TutorController::class)->name('proyectos.tutor');
});
