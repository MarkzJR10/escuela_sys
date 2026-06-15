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

            // --- ADMINISTRACIÓN ---
            $event->menu->add(['header' => 'ADMINISTRACIÓN']);
            
            // Usuarios (Desde DB si existe)
            $menuUsers = Menu::where('url', 'users')->first();
            if ($menuUsers && $user->can('users.index')) {
                $event->menu->add(['text' => 'Usuarios', 'url' => 'users', 'icon' => 'fas fa-fw fa-users']);
            } elseif ($user->hasRole('administrador')) {
                 $event->menu->add(['text' => 'Usuarios', 'url' => 'users', 'icon' => 'fas fa-fw fa-users']);
            }

            if ($user->hasRole('administrador')) {
                $event->menu->add(['text' => 'Roles y Permisos', 'url' => 'roles', 'icon' => 'fas fa-fw fa-user-shield']);
                $event->menu->add(['text' => 'Configuración General', 'url' => 'configuraciones', 'icon' => 'fas fa-fw fa-cogs']);
                $event->menu->add(['text' => 'Gestión de Menús', 'url' => 'menus', 'icon' => 'fas fa-fw fa-list']);
                $event->menu->add(['text' => 'Control de Periodos', 'url' => 'periodos', 'icon' => 'fas fa-fw fa-calendar-check']);
                $event->menu->add(['text' => 'Conceptos SAT', 'url' => 'sat_conceptos', 'icon' => 'fas fa-fw fa-file-invoice-dollar']);
            }

            // --- COORDINACIÓN ---
            $event->menu->add(['header' => 'COORDINACIÓN']);
            $event->menu->add(['text' => 'Alumnos', 'url' => 'alumnos', 'icon' => 'fas fa-fw fa-user-graduate']);
            $event->menu->add(['text' => 'Migrar Grados', 'url' => 'migrar_grados', 'icon' => 'fas fa-fw fa-exchange-alt']);
            $event->menu->add(['text' => 'Profesores', 'url' => 'profesores', 'icon' => 'fas fa-fw fa-chalkboard-teacher']);
            $event->menu->add(['text' => 'Asignar Maestro', 'url' => 'maestro_materia', 'icon' => 'fas fa-fw fa-link']);

            $event->menu->add(['text' => 'Grados y Grupos', 'url' => 'grado_grupos', 'icon' => 'fas fa-fw fa-layer-group']);
            $event->menu->add(['text' => 'Materias', 'url' => 'materias', 'icon' => 'fas fa-fw fa-book']);
            $event->menu->add(['text' => 'Cuadro de Honor', 'url' => 'cuadro-honor', 'icon' => 'fas fa-fw fa-trophy']);
            $event->menu->add(['text' => 'Boletas', 'url' => 'boletas', 'icon' => 'fas fa-fw fa-file-pdf']);


            // --- PADRE ---
            $event->menu->add(['header' => 'PADRE']);
            $event->menu->add(['text' => 'Padres de Familia', 'url' => 'padres', 'icon' => 'fas fa-fw fa-users-cog']);

            // --- SOCIO (Finanzas) ---
            $event->menu->add(['header' => 'SOCIO']);
            $event->menu->add(['text' => 'Colegiaturas', 'url' => 'colegiaturas', 'icon' => 'fas fa-fw fa-money-check-alt']);
            $event->menu->add([
                'text'    => 'Reportes Financieros',
                'icon'    => 'fas fa-fw fa-chart-bar',
                'submenu' => [
                    ['text' => 'Reporte de Cobranza', 'url' => 'reportes/cobranza', 'icon' => 'fas fa-fw fa-hand-holding-usd'],
                    ['text' => 'Pendientes por Mes', 'url' => 'reportes/pendientes-mes', 'icon' => 'fas fa-fw fa-calendar-times'],
                    ['text' => 'Historial Colegiaturas', 'url' => 'reportes/historial-colegiaturas', 'icon' => 'fas fa-fw fa-history'],
                ],
            ]);

            $event->menu->add(['text' => 'Cobros Especiales', 'url' => 'adeudos/especial', 'icon' => 'fas fa-fw fa-file-invoice']);
            $event->menu->add(['text' => 'Recargos Manuales', 'url' => 'adeudos/recargos-manual', 'icon' => 'fas fa-fw fa-exclamation-triangle']);
            $event->menu->add(['text' => 'Catálogo de Productos', 'url' => 'productos', 'icon' => 'fas fa-fw fa-box']);
            $event->menu->add(['text' => 'Punto de Venta (POS)', 'url' => 'pos', 'icon' => 'fas fa-fw fa-shopping-cart']);
            $event->menu->add(['text' => 'Auditoría de Ventas', 'url' => 'contabilidad/ventas', 'icon' => 'fas fa-fw fa-chart-line']);
            $event->menu->add(['text' => 'Estados de Cuenta', 'url' => 'cartera', 'icon' => 'fas fa-fw fa-search-dollar']);

            // --- MAESTRO / PROFESOR ---
            $event->menu->add(['header' => 'MAESTRO / PROFESOR']);
            $event->menu->add(['text' => 'Calificaciones', 'url' => 'calificaciones', 'icon' => 'fas fa-fw fa-star']);
            $event->menu->add(['text' => 'Asistencias', 'url' => 'asistencias', 'icon' => 'fas fa-fw fa-clipboard-check']);
            $event->menu->add([
                'text'    => 'Reportes de Conducta',
                'icon'    => 'fas fa-fw fa-exclamation-circle',
                'submenu' => [
                    ['text' => 'Capturar Reporte', 'url' => 'reportes_conducta/seleccionar', 'icon' => 'fas fa-fw fa-plus-circle'],
                    ['text' => 'Reportes del Día', 'url' => 'reportes_conducta', 'icon' => 'fas fa-fw fa-calendar-day'],
                    ['text' => 'Pendientes (No leídos)', 'url' => 'reportes_conducta/pendientes', 'icon' => 'fas fa-fw fa-envelope'],
                ],
            ]);
            $event->menu->add(['text' => 'Reportes de Tareas', 'url' => 'reportes_tareas', 'icon' => 'fas fa-fw fa-tasks']);

        });
    }
}
