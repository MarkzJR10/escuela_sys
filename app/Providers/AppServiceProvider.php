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

            // Intentar obtener todos los menús de la base de datos con sus roles
            try {
                $dbMenus = Menu::with('roles')->get()->keyBy('url');
            } catch (\Exception $e) {
                $dbMenus = collect();
            }

            // Obtener roles del usuario actual
            $userRoles = $user->roles->pluck('name')->toArray();

            // Tratamiento equivalente para 'maestro' y 'profesor'
            if (in_array('profesor', $userRoles) && !in_array('maestro', $userRoles)) {
                $userRoles[] = 'maestro';
            }
            if (in_array('maestro', $userRoles) && !in_array('profesor', $userRoles)) {
                $userRoles[] = 'profesor';
            }

            // Helper para comprobar acceso a un menú
            $hasAccess = function ($url) use ($dbMenus, $userRoles) {
                $menu = $dbMenus->get($url);
                if (!$menu) {
                    return in_array('administrador', $userRoles);
                }

                $menuRoles = $menu->roles->pluck('name')->toArray();

                // Tratamiento equivalente en los roles del menú
                if (in_array('profesor', $menuRoles) && !in_array('maestro', $menuRoles)) {
                    $menuRoles[] = 'maestro';
                }
                if (in_array('maestro', $menuRoles) && !in_array('profesor', $menuRoles)) {
                    $menuRoles[] = 'profesor';
                }

                return count(array_intersect($userRoles, $menuRoles)) > 0;
            };

            // Helper para obtener datos del menú
            $getMenuData = function ($url, $defaultText, $defaultIcon) use ($dbMenus) {
                $menu = $dbMenus->get($url);
                if ($menu) {
                    return [
                        'text' => $menu->text,
                        'url'  => $menu->url,
                        'icon' => $menu->icon,
                    ];
                }
                return [
                    'text' => $defaultText,
                    'url'  => $url,
                    'icon' => $defaultIcon,
                ];
            };

            // Helper para construir un ítem
            $buildItem = function ($url, $defaultText, $defaultIcon) use ($hasAccess, $getMenuData) {
                if ($hasAccess($url)) {
                    return $getMenuData($url, $defaultText, $defaultIcon);
                }
                return null;
            };

            // =============================================
            // ADMINISTRACIÓN
            // =============================================
            $adminItems = [];
            if ($item = $buildItem('users', 'Usuarios', 'fas fa-fw fa-users')) {
                $adminItems[] = $item;
            }
            if ($item = $buildItem('roles', 'Roles y Permisos', 'fas fa-fw fa-user-shield')) {
                $adminItems[] = $item;
            }
            if ($item = $buildItem('bitacora', 'Bitácora de Auditoría', 'fas fa-fw fa-history')) {
                $adminItems[] = $item;
            }

            // Submenú Configuración
            $configSubmenu = [];
            if ($item = $buildItem('configuraciones', 'General', 'fas fa-fw fa-sliders-h')) {
                $configSubmenu[] = $item;
            }
            if ($item = $buildItem('configuraciones/visibilidad-portal-padres', 'Visibilidad Portal Padres', 'fas fa-fw fa-user-shield')) {
                $configSubmenu[] = $item;
            }
            if ($item = $buildItem('menus', 'Gestión de Menús', 'fas fa-fw fa-list')) {
                $configSubmenu[] = $item;
            }
            if ($item = $buildItem('periodos', 'Control de Periodos', 'fas fa-fw fa-calendar-check')) {
                $configSubmenu[] = $item;
            }
            if ($item = $buildItem('sat_conceptos', 'Conceptos SAT', 'fas fa-fw fa-file-invoice-dollar')) {
                $configSubmenu[] = $item;
            }
            if ($item = $buildItem('colegiaturas-config', 'Catálogo de Colegiaturas', 'fas fa-fw fa-money-bill-alt')) {
                $configSubmenu[] = $item;
            }

            if (!empty($configSubmenu)) {
                $adminItems[] = [
                    'text'    => 'Configuración',
                    'icon'    => 'fas fa-fw fa-cogs',
                    'submenu' => $configSubmenu,
                ];
            }

            if (!empty($adminItems)) {
                $event->menu->add(['header' => 'ADMINISTRACIÓN']);
                foreach ($adminItems as $item) {
                    $event->menu->add($item);
                }
            }

            // =============================================
            // COORDINACIÓN
            // =============================================
            $coordItems = [];
            if ($item = $buildItem('alumnos', 'Alumnos', 'fas fa-fw fa-user-graduate')) {
                $coordItems[] = $item;
            }

            // Submenú Gestión Escolar
            $escolarSubmenu = [];
            if ($item = $buildItem('grado_grupos', 'Grados y Grupos', 'fas fa-fw fa-layer-group')) {
                $escolarSubmenu[] = $item;
            }
            if ($item = $buildItem('materias', 'Materias', 'fas fa-fw fa-book')) {
                $escolarSubmenu[] = $item;
            }
            if ($item = $buildItem('migrar_grados', 'Migrar Grados', 'fas fa-fw fa-exchange-alt')) {
                $escolarSubmenu[] = $item;
            }

            if (!empty($escolarSubmenu)) {
                $coordItems[] = [
                    'text'    => 'Gestión Escolar',
                    'icon'    => 'fas fa-fw fa-school',
                    'submenu' => $escolarSubmenu,
                ];
            }

            // Submenú Profesores
            $profesoresSubmenu = [];
            if ($item = $buildItem('profesores', 'Lista de Profesores', 'fas fa-fw fa-chalkboard-teacher')) {
                $profesoresSubmenu[] = $item;
            }
            if ($item = $buildItem('maestro_materia', 'Asignar Maestro-Materia', 'fas fa-fw fa-link')) {
                $profesoresSubmenu[] = $item;
            }
            if ($item = $buildItem('maestro_grupo', 'Asignar Maestro de Planta', 'fas fa-fw fa-home')) {
                $profesoresSubmenu[] = $item;
            }
            if ($item = $buildItem('asistencia-maestros', 'Asistencia Maestros', 'fas fa-fw fa-clock')) {
                $profesoresSubmenu[] = $item;
            }

            if (!empty($profesoresSubmenu)) {
                $coordItems[] = [
                    'text'    => 'Profesores',
                    'icon'    => 'fas fa-fw fa-chalkboard-teacher',
                    'submenu' => $profesoresSubmenu,
                ];
            }

            if ($item = $buildItem('padres', 'Padres de Familia', 'fas fa-fw fa-users-cog')) {
                $coordItems[] = $item;
            }

            if (!empty($coordItems)) {
                $event->menu->add(['header' => 'COORDINACIÓN']);
                foreach ($coordItems as $item) {
                    $event->menu->add($item);
                }
            }

            // =============================================
            // ACADÉMICO
            // =============================================
            $acadItems = [];
            if ($item = $buildItem('calificaciones', 'Calificaciones', 'fas fa-fw fa-star')) {
                $acadItems[] = $item;
            }
            if ($item = $buildItem('asistencias', 'Asistencias', 'fas fa-fw fa-clipboard-check')) {
                $acadItems[] = $item;
            }

            // Submenú Conducta
            $conductaSubmenu = [];
            if ($item = $buildItem('reportes_conducta/seleccionar', 'Capturar Reporte', 'fas fa-fw fa-plus-circle')) {
                $conductaSubmenu[] = $item;
            }
            if ($item = $buildItem('reportes_conducta', 'Reportes del Día', 'fas fa-fw fa-calendar-day')) {
                $conductaSubmenu[] = $item;
            }
            if ($item = $buildItem('reportes_conducta/pendientes', 'Pendientes (No leídos)', 'fas fa-fw fa-envelope')) {
                $conductaSubmenu[] = $item;
            }
            if ($item = $buildItem('conducta-destacada', 'Conducta Destacada', 'fas fa-fw fa-star-half-alt')) {
                $conductaSubmenu[] = $item;
            }

            if (!empty($conductaSubmenu)) {
                $acadItems[] = [
                    'text'    => 'Conducta / Tareas',
                    'icon'    => 'fas fa-fw fa-exclamation-circle',
                    'submenu' => $conductaSubmenu,
                ];
            }

            if ($item = $buildItem('reportes_tareas', 'Reportes de Tareas', 'fas fa-fw fa-tasks')) {
                $acadItems[] = $item;
            }
            if ($item = $buildItem('boletas', 'Boletas', 'fas fa-fw fa-file-pdf')) {
                $acadItems[] = $item;
            }
            if ($item = $buildItem('cuadro-honor', 'Cuadro de Honor', 'fas fa-fw fa-trophy')) {
                $acadItems[] = $item;
            }

            if (!empty($acadItems)) {
                $event->menu->add(['header' => 'ACADÉMICO']);
                foreach ($acadItems as $item) {
                    $event->menu->add($item);
                }
            }

            // =============================================
            // FINANZAS
            // =============================================
            $finItems = [];
            if ($item = $buildItem('pos', 'Punto de Venta (POS)', 'fas fa-fw fa-cash-register')) {
                $finItems[] = $item;
            }

            // Submenú Cobranza
            $cobranzaSubmenu = [];
            if ($item = $buildItem('colegiaturas', 'Control de Colegiaturas', 'fas fa-fw fa-money-bill-wave')) {
                $cobranzaSubmenu[] = $item;
            }
            if ($item = $buildItem('adeudos/especial', 'Cobros Especiales', 'fas fa-fw fa-file-invoice')) {
                $cobranzaSubmenu[] = $item;
            }
            if ($item = $buildItem('ciclos', 'Ciclos Masivo', 'fas fa-fw fa-calendar-alt')) {
                $cobranzaSubmenu[] = $item;
            }
            if ($item = $buildItem('adeudos/recargos-manual', 'Recargos Manuales', 'fas fa-fw fa-exclamation-triangle')) {
                $cobranzaSubmenu[] = $item;
            }
            if ($item = $buildItem('cartera', 'Estados de Cuenta', 'fas fa-fw fa-search-dollar')) {
                $cobranzaSubmenu[] = $item;
            }
            if ($item = $buildItem('extracurriculares', 'Clases Extracurriculares', 'fas fa-fw fa-running')) {
                $cobranzaSubmenu[] = $item;
            }

            if (!empty($cobranzaSubmenu)) {
                $finItems[] = [
                    'text'    => 'Cobranza',
                    'icon'    => 'fas fa-fw fa-money-check-alt',
                    'submenu' => $cobranzaSubmenu,
                ];
            }

            // Submenú Reportes Financieros
            $repFinSubmenu = [];
            if ($item = $buildItem('reportes/cobranza', 'Reporte de Cobranza', 'fas fa-fw fa-hand-holding-usd')) {
                $repFinSubmenu[] = $item;
            }
            if ($item = $buildItem('reportes/pendientes-mes', 'Pendientes por Mes', 'fas fa-fw fa-calendar-times')) {
                $repFinSubmenu[] = $item;
            }
            if ($item = $buildItem('reportes/historial-colegiaturas', 'Historial Colegiaturas', 'fas fa-fw fa-history')) {
                $repFinSubmenu[] = $item;
            }
            if ($item = $buildItem('reportes/adeudos-especiales', 'Adeudos Especiales', 'fas fa-fw fa-star')) {
                $repFinSubmenu[] = $item;
            }
            if ($item = $buildItem('reportes/exportar-saldos', 'Exportar Saldos (Excel)', 'fas fa-fw fa-file-excel')) {
                $repFinSubmenu[] = $item;
            }

            if (!empty($repFinSubmenu)) {
                $finItems[] = [
                    'text'    => 'Reportes Financieros',
                    'icon'    => 'fas fa-fw fa-chart-bar',
                    'submenu' => $repFinSubmenu,
                ];
            }

            // Submenú Contabilidad y Cajas
            $contSubmenu = [];
            if ($item = $buildItem('contabilidad/ventas', 'Lista de Ventas', 'fas fa-fw fa-list')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/ventas-canceladas', 'Ventas Canceladas', 'fas fa-fw fa-ban')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/ventas-por-fecha', 'Ventas por Fecha', 'fas fa-fw fa-calendar-day')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/ventas-producto', 'Ventas por Producto', 'fas fa-fw fa-boxes')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/reporte-producto', 'Reporte por Producto', 'fas fa-fw fa-boxes')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/efectivo-cajas', 'Efectivo en Cajas', 'fas fa-fw fa-cash-register')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/discrepancias', 'Discrepancias', 'fas fa-fw fa-exclamation-circle')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/gastos', 'Gastos', 'fas fa-fw fa-file-invoice')) {
                $contSubmenu[] = $item;
            }
            if ($item = $buildItem('contabilidad/corte-caja', 'Corte de Caja', 'fas fa-fw fa-file-invoice-dollar')) {
                $contSubmenu[] = $item;
            }

            if (!empty($contSubmenu)) {
                $finItems[] = [
                    'text'    => 'Contabilidad y Cajas',
                    'icon'    => 'fas fa-fw fa-calculator',
                    'submenu' => $contSubmenu,
                ];
            }

            if ($item = $buildItem('productos', 'Catálogo de Productos', 'fas fa-fw fa-box')) {
                $finItems[] = $item;
            }
            if ($item = $buildItem('importar-pagos', 'Importar Pagos (Excel)', 'fas fa-fw fa-file-import')) {
                $finItems[] = $item;
            }

            if (!empty($finItems)) {
                $event->menu->add(['header' => 'FINANZAS']);
                foreach ($finItems as $item) {
                    $event->menu->add($item);
                }
            }

            // =============================================
            // MI PORTAL
            // =============================================
            $padreItems = [];
            if ($item = $buildItem('portal-padre/dashboard', 'Mis Hijos', 'fas fa-fw fa-child')) {
                $padreItems[] = $item;
            }

            if (!empty($padreItems)) {
                $event->menu->add(['header' => 'MI PORTAL']);
                foreach ($padreItems as $item) {
                    $event->menu->add($item);
                }
            }
        });
    }
}
