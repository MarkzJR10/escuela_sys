<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Models\Menu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {
            $user = Auth::user();
            if (!$user) return;

            $isAdmin = $user->hasRole('administrador');
            $isCoord = $user->hasRole('coordinador');
            $isMaestro = $user->hasRole('maestro');
            $isSocio = $user->hasRole('socio');
            $isCajero = $user->hasRole('cajero');
            $isPadre = $user->hasRole('padre');

            // Roles con acceso administrativo/operativo
            $canManage = $isAdmin || $isCoord;
            $canFinance = $isAdmin || $isSocio || $isCajero;
            $canTeach = $isAdmin || $isCoord || $isMaestro;

            // =============================================
            // ADMINISTRACIÓN (solo administrador)
            // =============================================
            if ($isAdmin) {
                $event->menu->add(['header' => 'ADMINISTRACIÓN']);
                $event->menu->add(['text' => 'Usuarios', 'url' => 'users', 'icon' => 'fas fa-fw fa-users']);
                $event->menu->add(['text' => 'Roles y Permisos', 'url' => 'roles', 'icon' => 'fas fa-fw fa-user-shield']);
                $event->menu->add([
                    'text'    => 'Configuración',
                    'icon'    => 'fas fa-fw fa-cogs',
                    'submenu' => [
                        ['text' => 'General', 'url' => 'configuraciones', 'icon' => 'fas fa-fw fa-sliders-h'],
                        ['text' => 'Gestión de Menús', 'url' => 'menus', 'icon' => 'fas fa-fw fa-list'],
                        ['text' => 'Control de Periodos', 'url' => 'periodos', 'icon' => 'fas fa-fw fa-calendar-check'],
                        ['text' => 'Conceptos SAT', 'url' => 'sat_conceptos', 'icon' => 'fas fa-fw fa-file-invoice-dollar'],
                    ],
                ]);
            }

            // =============================================
            // COORDINACIÓN (admin + coordinador)
            // =============================================
            if ($canManage) {
                $event->menu->add(['header' => 'COORDINACIÓN']);
                $event->menu->add(['text' => 'Alumnos', 'url' => 'alumnos', 'icon' => 'fas fa-fw fa-user-graduate']);
                $event->menu->add([
                    'text'    => 'Gestión Escolar',
                    'icon'    => 'fas fa-fw fa-school',
                    'submenu' => [
                        ['text' => 'Grados y Grupos', 'url' => 'grado_grupos', 'icon' => 'fas fa-fw fa-layer-group'],
                        ['text' => 'Materias', 'url' => 'materias', 'icon' => 'fas fa-fw fa-book'],
                        ['text' => 'Turnos', 'url' => 'turnos', 'icon' => 'fas fa-fw fa-sun'],
                        ['text' => 'Migrar Grados', 'url' => 'migrar_grados', 'icon' => 'fas fa-fw fa-exchange-alt'],
                    ],
                ]);
                $event->menu->add([
                    'text'    => 'Profesores',
                    'icon'    => 'fas fa-fw fa-chalkboard-teacher',
                    'submenu' => [
                        ['text' => 'Lista de Profesores', 'url' => 'profesores', 'icon' => 'fas fa-fw fa-chalkboard-teacher'],
                        ['text' => 'Asignar Maestro-Materia', 'url' => 'maestro_materia', 'icon' => 'fas fa-fw fa-link'],
                        ['text' => 'Asistencia Maestros', 'url' => 'asistencia-maestros', 'icon' => 'fas fa-fw fa-clock'],
                    ],
                ]);
                $event->menu->add(['text' => 'Padres de Familia', 'url' => 'padres', 'icon' => 'fas fa-fw fa-users-cog']);
            }

            // =============================================
            // ACADÉMICO (admin + coordinador + maestro)
            // =============================================
            if ($canTeach) {
                $event->menu->add(['header' => 'ACADÉMICO']);
                $event->menu->add(['text' => 'Calificaciones', 'url' => 'calificaciones', 'icon' => 'fas fa-fw fa-star']);
                $event->menu->add(['text' => 'Asistencias', 'url' => 'asistencias', 'icon' => 'fas fa-fw fa-clipboard-check']);
                $event->menu->add([
                    'text'    => 'Conducta',
                    'icon'    => 'fas fa-fw fa-exclamation-circle',
                    'submenu' => [
                        ['text' => 'Capturar Reporte', 'url' => 'reportes_conducta/seleccionar', 'icon' => 'fas fa-fw fa-plus-circle'],
                        ['text' => 'Reportes del Día', 'url' => 'reportes_conducta', 'icon' => 'fas fa-fw fa-calendar-day'],
                        ['text' => 'Pendientes (No leídos)', 'url' => 'reportes_conducta/pendientes', 'icon' => 'fas fa-fw fa-envelope'],
                        ['text' => 'Conducta Destacada', 'url' => 'conducta-destacada', 'icon' => 'fas fa-fw fa-star-half-alt'],
                    ],
                ]);
                $event->menu->add(['text' => 'Reportes de Tareas', 'url' => 'reportes_tareas', 'icon' => 'fas fa-fw fa-tasks']);
                $event->menu->add(['text' => 'Boletas', 'url' => 'boletas', 'icon' => 'fas fa-fw fa-file-pdf']);
                $event->menu->add(['text' => 'Cuadro de Honor', 'url' => 'cuadro-honor', 'icon' => 'fas fa-fw fa-trophy']);
            }

            // =============================================
            // FINANZAS (admin + socio + cajero)
            // =============================================
            if ($canFinance) {
                $event->menu->add(['header' => 'FINANZAS']);
                $event->menu->add(['text' => 'Punto de Venta (POS)', 'url' => 'pos', 'icon' => 'fas fa-fw fa-cash-register']);
                $event->menu->add([
                    'text'    => 'Cobranza',
                    'icon'    => 'fas fa-fw fa-money-check-alt',
                    'submenu' => [
                        ['text' => 'Control de Colegiaturas', 'url' => 'colegiaturas', 'icon' => 'fas fa-fw fa-money-bill-wave'],
                        ['text' => 'Cobros Especiales', 'url' => 'adeudos/especial', 'icon' => 'fas fa-fw fa-file-invoice'],
                        ['text' => 'Ciclos Masivo', 'url' => 'ciclos', 'icon' => 'fas fa-fw fa-calendar-alt'],
                        ['text' => 'Recargos Manuales', 'url' => 'adeudos/recargos-manual', 'icon' => 'fas fa-fw fa-exclamation-triangle'],
                        ['text' => 'Estados de Cuenta', 'url' => 'cartera', 'icon' => 'fas fa-fw fa-search-dollar'],
                    ],
                ]);
                $event->menu->add([
                    'text'    => 'Reportes Financieros',
                    'icon'    => 'fas fa-fw fa-chart-bar',
                    'submenu' => [
                        ['text' => 'Reporte de Cobranza', 'url' => 'reportes/cobranza', 'icon' => 'fas fa-fw fa-hand-holding-usd'],
                        ['text' => 'Pendientes por Mes', 'url' => 'reportes/pendientes-mes', 'icon' => 'fas fa-fw fa-calendar-times'],
                        ['text' => 'Historial Colegiaturas', 'url' => 'reportes/historial-colegiaturas', 'icon' => 'fas fa-fw fa-history'],
                        ['text' => 'Exportar Saldos (Excel)', 'url' => 'reportes/exportar-saldos', 'icon' => 'fas fa-fw fa-file-excel'],
                    ],
                ]);
                $event->menu->add([
                    'text'    => 'Contabilidad y Cajas',
                    'icon'    => 'fas fa-fw fa-calculator',
                    'submenu' => [
                        ['text' => 'Lista de Ventas', 'url' => 'contabilidad/ventas', 'icon' => 'fas fa-fw fa-list'],
                        ['text' => 'Ventas Canceladas', 'url' => 'contabilidad/ventas-canceladas', 'icon' => 'fas fa-fw fa-ban'],
                        ['text' => 'Ventas por Fecha', 'url' => 'contabilidad/ventas-por-fecha', 'icon' => 'fas fa-fw fa-calendar-day'],
                        ['text' => 'Ventas por Producto', 'url' => 'contabilidad/ventas-producto', 'icon' => 'fas fa-fw fa-boxes'],
                        ['text' => 'Efectivo en Cajas', 'url' => 'contabilidad/efectivo-cajas', 'icon' => 'fas fa-fw fa-cash-register'],
                        ['text' => 'Discrepancias', 'url' => 'contabilidad/discrepancias', 'icon' => 'fas fa-fw fa-exclamation-circle'],
                        ['text' => 'Gastos', 'url' => 'contabilidad/gastos', 'icon' => 'fas fa-fw fa-file-invoice'],
                    ],
                ]);
                $event->menu->add(['text' => 'Catálogo de Productos', 'url' => 'productos', 'icon' => 'fas fa-fw fa-box']);
                $event->menu->add(['text' => 'Importar Pagos (Excel)', 'url' => 'importar-pagos', 'icon' => 'fas fa-fw fa-file-import']);
            }

            // =============================================
            // PORTAL PADRE (solo padre)
            // =============================================
            if ($isPadre) {
                $event->menu->add(['header' => 'MI PORTAL']);
                $event->menu->add(['text' => 'Mis Hijos', 'url' => 'portal-padre/dashboard', 'icon' => 'fas fa-fw fa-child']);
            }

        });
    }
}
