<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::get('login/google', [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback']);


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
use App\Http\Controllers\MaestroGrupoController;
use App\Http\Controllers\ReporteCobranzaController;
use App\Http\Controllers\ContabilidadController;
use App\Http\Controllers\AsistenciaMaestroController;
use App\Http\Controllers\PortalPadreController;
use App\Http\Controllers\ComplementosController;
use App\Http\Controllers\CicloController;
use App\Http\Controllers\ColegiaturaConfigController;


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
    Route::get('/cuadro-honor/diploma/{alumno}', [CuadroHonorController::class, 'generarDiploma'])->name('cuadro_honor.diploma');
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
    Route::post('adeudos/{adeudo}/cancelar', [AdeudoController::class, 'cancelarAdeudo'])->name('adeudos.cancelar');

    // Rutas de Pagos (Caja)
    Route::get('pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('pagos/create/{alumno}', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('pagos/store/{alumno}', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('pagos/ticket/{pago}', [PagoController::class, 'ticket'])->name('pagos.ticket');

    // Redirigir /adeudos al módulo de colegiaturas (evitar 404)
    Route::get('adeudos', function () {
        return redirect()->route('colegiaturas.index');
    })->name('adeudos.index');

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
    Route::get('maestro_grupo', [MaestroGrupoController::class, 'index'])->name('maestro_grupo.index');
    Route::post('maestro_grupo', [MaestroGrupoController::class, 'store'])->name('maestro_grupo.store');
    Route::delete('maestro_grupo/{gradoGrupo}', [MaestroGrupoController::class, 'destroy'])->name('maestro_grupo.destroy');

    Route::get('reportes/cobranza', [ReporteCobranzaController::class, 'index'])->name('reportes.cobranza');
    Route::get('reportes/pendientes-mes', [ReporteCobranzaController::class, 'pendientesPorMes'])->name('reportes.pendientes_mes');
    Route::get('reportes/estado-cuenta', [ReporteCobranzaController::class, 'estadoCuenta'])->name('reportes.estado_cuenta');
    Route::get('reportes/detalle-alumno/{alumno}', [ReporteCobranzaController::class, 'detalleAlumno'])->name('reportes.detalle_alumno');
    Route::get('reportes/historial-colegiaturas', [ReporteCobranzaController::class, 'historialColegiaturas'])->name('reportes.historial_colegiaturas');
    Route::get('reportes/adeudos-especiales', [ReporteCobranzaController::class, 'adeudosEspeciales'])->name('reportes.adeudos_especiales');
    Route::get('reportes/exportar-saldos', [ComplementosController::class, 'exportarSaldosExcel'])->name('reportes.exportar_saldos');

    // Fase 4: Portal Padre
    Route::prefix('portal-padre')->name('portal_padre.')->group(function () {
        Route::get('dashboard', [PortalPadreController::class, 'dashboard'])->name('dashboard');
        Route::get('hijo/{alumno}/boleta', [PortalPadreController::class, 'boleta'])->name('boleta');
        Route::get('hijo/{alumno}/conducta', [PortalPadreController::class, 'conducta'])->name('conducta');
        Route::get('hijo/{alumno}/estado-cuenta', [PortalPadreController::class, 'estadoCuenta'])->name('estado_cuenta');
    });

    // Fase 4: PDF y Excel
    Route::get('boletas/{alumno}/pdf', [ComplementosController::class, 'generarBoletaPdf'])->name('boletas.pdf');
    Route::post('asistencias/imprimir-lista', [ComplementosController::class, 'imprimirListaAsistencia'])->name('asistencias.imprimir_lista');
    Route::get('conducta-destacada', [ComplementosController::class, 'conductaDestacada'])->name('conducta_destacada');
    Route::get('importar-pagos', [ComplementosController::class, 'showImportarPagos'])->name('complementos.importar_pagos');
    Route::post('importar-pagos', [ComplementosController::class, 'procesarImportarPagos'])->name('complementos.importar_pagos.post');

    // Ciclos Masivo
    Route::get('ciclos', [CicloController::class, 'index'])->name('ciclos.index');
    Route::post('ciclos/registrar-masivo', [CicloController::class, 'registrarMasivo'])->name('ciclos.registrar_masivo');
    Route::post('ciclos/registrar-reinscripcion-masivo', [CicloController::class, 'registrarReinscripcionMasivo'])->name('ciclos.registrar_reinscripcion_masivo');
    Route::post('ciclos/eliminar-masivo', [CicloController::class, 'eliminarMasivo'])->name('ciclos.eliminar_masivo');
    
    // Fase 4: Turnos y Asistencia Maestros
    Route::get('asistencia-maestros', [AsistenciaMaestroController::class, 'index'])->name('asistencia_maestros.index');
    Route::post('asistencia-maestros', [AsistenciaMaestroController::class, 'store'])->name('asistencia_maestros.store');

    // Fase 3: Contabilidad y Cajas
    Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
        Route::get('ventas', [ContabilidadController::class, 'listaVentas'])->name('ventas');
        Route::post('ventas/{pago}/cancelar', [ContabilidadController::class, 'cancelarTicket'])->name('ventas.cancelar');
        Route::get('ventas-canceladas', [ContabilidadController::class, 'ventasCanceladas'])->name('ventas_canceladas');
        Route::get('ventas-por-fecha', [ContabilidadController::class, 'ventasPorFecha'])->name('ventas_por_fecha');
        Route::get('ventas-producto', [ContabilidadController::class, 'ventasProductoFecha'])->name('ventas_producto');
        Route::get('efectivo-cajas', [ContabilidadController::class, 'efectivoCajas'])->name('efectivo_cajas');
        
        Route::get('discrepancias', [ContabilidadController::class, 'discrepancias'])->name('discrepancias.index');
        Route::post('discrepancias', [ContabilidadController::class, 'storeDiscrepancia'])->name('discrepancias.store');

        Route::get('gastos', [ContabilidadController::class, 'gastos'])->name('gastos.index');
        Route::post('gastos', [ContabilidadController::class, 'storeGasto'])->name('gastos.store');

        Route::get('corte-caja', [ContabilidadController::class, 'corteCaja'])->name('corte_caja');
        Route::post('corte-caja', [ContabilidadController::class, 'storeCorteCaja'])->name('corte_caja.store');
        Route::get('corte-caja/{corte}/pdf', [ContabilidadController::class, 'pdfCorteCaja'])->name('corte_caja.pdf');
    });

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

        // CRUD Catálogo de Colegiaturas
        Route::resource('colegiaturas-config', ColegiaturaConfigController::class);
    });
});
