<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\GradoGrupoController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\PeriodoControlController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\ColegiaturaController;
use App\Http\Controllers\AdeudoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CarteraController;
use App\Http\Controllers\PadreController;
use App\Http\Controllers\SatConceptoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CuadroHonorController;
use App\Http\Controllers\ReporteConductaController;
use App\Http\Controllers\ReporteTareaController;
use App\Http\Controllers\BoletaController;
use App\Http\Controllers\MigrarGradoController;
use App\Http\Controllers\MaestroMateriaController;
use App\Http\Controllers\ReporteCobranzaController;

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('alumnos', AlumnoController::class);
    Route::resource('grado_grupos', GradoGrupoController::class);
    Route::resource('materias', MateriaController::class);
    Route::resource('profesores', ProfesorController::class);
    Route::resource('calificaciones', CalificacionController::class);
    
    // Cuadro de Honor
    Route::get('/cuadro-honor', [CuadroHonorController::class, 'index'])->name('cuadro_honor.index');
    Route::resource('asistencias', AsistenciaController::class);
    Route::resource('padres', PadreController::class);
    Route::post('padres/{padre}/billing', [PadreController::class, 'updateBilling'])->name('padres.billing');
    Route::get('padres/{padre}/children', [PadreController::class, 'getChildren'])->name('padres.children');

    // Cartera / Estados de Cuenta
    Route::get('cartera', [CarteraController::class, 'index'])->name('cartera.index');

    // Rutas de Colegiaturas y Adeudos
    Route::get('colegiaturas', [ColegiaturaController::class, 'index'])->name('colegiaturas.index');
    Route::patch('colegiaturas/{alumno}', [ColegiaturaController::class, 'update'])->name('colegiaturas.update');
    Route::get('colegiaturas/{alumno}/adeudos', [ColegiaturaController::class, 'adeudos'])->name('colegiaturas.adeudos');

    // Rutas de Adeudos Especiales
    Route::get('adeudos/especial', [AdeudoController::class, 'createEspecial'])->name('adeudos.create_especial');
    Route::post('adeudos/especial', [AdeudoController::class, 'storeEspecial'])->name('adeudos.store_especial');
    Route::get('adeudos/buscar-alumnos', [AdeudoController::class, 'buscarAlumnosAjax'])->name('adeudos.buscar_alumnos');
    Route::get('adeudos/recargos-manual', [AdeudoController::class, 'showRecargosManual'])->name('adeudos.recargos_manual');
    Route::post('adeudos/recargos-manual', [AdeudoController::class, 'ejecutarRecargosManual'])->name('adeudos.ejecutar_recargos_manual');

    // Rutas de Pagos (Caja)
    Route::get('pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('pagos/create/{alumno}', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('pagos/store/{alumno}', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('pagos/ticket/{pago}', [PagoController::class, 'ticket'])->name('pagos.ticket');

    // Rutas de Contabilidad
    Route::get('contabilidad/ventas', [PagoController::class, 'reporte'])->name('contabilidad.ventas');

    // Rutas de Punto de Venta (POS)
    Route::get('pos', [POSController::class, 'index'])->name('pos.index');
    Route::get('pos/buscar-alumno', [POSController::class, 'buscarAlumno'])->name('pos.buscar_alumno');
    Route::get('pos/adeudos/{alumno}', [POSController::class, 'getAdeudos'])->name('pos.get_adeudos');
    Route::post('pos/procesar', [POSController::class, 'procesar'])->name('pos.procesar');

    // Catálogo de Productos
    Route::resource('productos', ProductoController::class);

    // Nuevas funcionalidades (Fase 1 y 2)
    Route::get('reportes_conducta/pendientes', [ReporteConductaController::class, 'pendientes'])->name('reportes_conducta.pendientes');
    Route::get('reportes_conducta/alumno/{alumno}', [ReporteConductaController::class, 'porAlumno'])->name('reportes_conducta.por_alumno');
    Route::get('reportes_conducta/seleccionar', [ReporteConductaController::class, 'seleccionarAlumno'])->name('reportes_conducta.seleccionar');
    Route::resource('reportes_conducta', ReporteConductaController::class);

    Route::resource('reportes_tareas', ReporteTareaController::class);

    Route::get('boletas/gestionar/{alumno}', [BoletaController::class, 'gestionar'])->name('boletas.gestionar');
    Route::post('boletas/gestionar/{alumno}', [BoletaController::class, 'storeMateria'])->name('boletas.store_materia');
    Route::resource('boletas', BoletaController::class);

    Route::get('migrar_grados/inactivos', [MigrarGradoController::class, 'inactivos'])->name('migrar_grados.inactivos');
    Route::post('migrar_grados/dar_baja', [MigrarGradoController::class, 'darBaja'])->name('migrar_grados.dar_baja');
    Route::post('migrar_grados/reactivar', [MigrarGradoController::class, 'reactivar'])->name('migrar_grados.reactivar');
    Route::post('migrar_grados/alumno', [MigrarGradoController::class, 'migrarAlumno'])->name('migrar_grados.alumno');
    Route::post('migrar_grados/masivo', [MigrarGradoController::class, 'migrarMasivo'])->name('migrar_grados.masivo');
    Route::get('migrar_grados', [MigrarGradoController::class, 'index'])->name('migrar_grados.index');

    Route::resource('maestro_materia', MaestroMateriaController::class)->only(['index', 'store', 'destroy']);

    Route::get('reportes/cobranza', [ReporteCobranzaController::class, 'index'])->name('reportes.cobranza');
    Route::get('reportes/pendientes-mes', [ReporteCobranzaController::class, 'pendientesPorMes'])->name('reportes.pendientes_mes');
    Route::get('reportes/estado-cuenta', [ReporteCobranzaController::class, 'estadoCuenta'])->name('reportes.estado_cuenta');
    Route::get('reportes/detalle-alumno/{alumno}', [ReporteCobranzaController::class, 'detalleAlumno'])->name('reportes.detalle_alumno');
    Route::get('reportes/historial-colegiaturas', [ReporteCobranzaController::class, 'historialColegiaturas'])->name('reportes.historial_colegiaturas');

    Route::middleware(['role:administrador'])->group(function () {
        Route::resource('menus', MenuController::class);
        Route::get('calificaciones/captura', [CalificacionController::class, 'captura'])->name('calificaciones.captura')->withoutMiddleware(['role:administrador']);
        Route::post('calificaciones/bulk', [CalificacionController::class, 'bulkStore'])->name('calificaciones.bulk_store')->withoutMiddleware(['role:administrador']);
        Route::get('periodos', [PeriodoControlController::class, 'index'])->name('periodos.index');
        Route::post('periodos/{periodoControl}/toggle', [PeriodoControlController::class, 'toggle'])->name('periodos.toggle');
        Route::resource('roles', RoleController::class);
        Route::resource('sat_conceptos', SatConceptoController::class);

        // Rutas de Configuración General
        Route::get('configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
        Route::post('configuraciones', [ConfiguracionController::class, 'update'])->name('configuraciones.update');
    });
});
