<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Menu;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['text' => 'Usuarios', 'url' => 'users', 'icon' => 'fas fa-fw fa-users', 'roles' => ['administrador']],
            ['text' => 'Roles y Permisos', 'url' => 'roles', 'icon' => 'fas fa-fw fa-user-shield', 'roles' => ['administrador']],
            ['text' => 'Bitácora de Auditoría', 'url' => 'bitacora', 'icon' => 'fas fa-fw fa-history', 'roles' => ['administrador']],
            ['text' => 'General', 'url' => 'configuraciones', 'icon' => 'fas fa-fw fa-sliders-h', 'roles' => ['administrador']],
            ['text' => 'Visibilidad Portal Padres', 'url' => 'configuraciones/visibilidad-portal-padres', 'icon' => 'fas fa-fw fa-user-shield', 'roles' => ['administrador']],
            ['text' => 'Gestión de Menús', 'url' => 'menus', 'icon' => 'fas fa-fw fa-list', 'roles' => ['administrador']],
            ['text' => 'Control de Periodos', 'url' => 'periodos', 'icon' => 'fas fa-fw fa-calendar-check', 'roles' => ['administrador']],
            ['text' => 'Conceptos SAT', 'url' => 'sat_conceptos', 'icon' => 'fas fa-fw fa-file-invoice-dollar', 'roles' => ['administrador']],
            ['text' => 'Catálogo de Colegiaturas', 'url' => 'colegiaturas-config', 'icon' => 'fas fa-fw fa-money-bill-alt', 'roles' => ['administrador']],
            ['text' => 'Alumnos', 'url' => 'alumnos', 'icon' => 'fas fa-fw fa-user-graduate', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Grados y Grupos', 'url' => 'grado_grupos', 'icon' => 'fas fa-fw fa-layer-group', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Materias', 'url' => 'materias', 'icon' => 'fas fa-fw fa-book', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Migrar Grados', 'url' => 'migrar_grados', 'icon' => 'fas fa-fw fa-exchange-alt', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Lista de Profesores', 'url' => 'profesores', 'icon' => 'fas fa-fw fa-chalkboard-teacher', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Asignar Maestro-Materia', 'url' => 'maestro_materia', 'icon' => 'fas fa-fw fa-link', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Asignar Maestro de Planta', 'url' => 'maestro_grupo', 'icon' => 'fas fa-fw fa-home', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Asistencia Maestros', 'url' => 'asistencia-maestros', 'icon' => 'fas fa-fw fa-clock', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Padres de Familia', 'url' => 'padres', 'icon' => 'fas fa-fw fa-users-cog', 'roles' => ['administrador', 'coordinador']],
            ['text' => 'Calificaciones', 'url' => 'calificaciones', 'icon' => 'fas fa-fw fa-star', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Asistencias', 'url' => 'asistencias', 'icon' => 'fas fa-fw fa-clipboard-check', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Capturar Reporte', 'url' => 'reportes_conducta/seleccionar', 'icon' => 'fas fa-fw fa-plus-circle', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Reportes del Día', 'url' => 'reportes_conducta', 'icon' => 'fas fa-fw fa-calendar-day', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Pendientes (No leídos)', 'url' => 'reportes_conducta/pendientes', 'icon' => 'fas fa-fw fa-envelope', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Conducta Destacada', 'url' => 'conducta-destacada', 'icon' => 'fas fa-fw fa-star-half-alt', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Reportes de Tareas', 'url' => 'reportes_tareas', 'icon' => 'fas fa-fw fa-tasks', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Boletas', 'url' => 'boletas', 'icon' => 'fas fa-fw fa-file-pdf', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Cuadro de Honor', 'url' => 'cuadro-honor', 'icon' => 'fas fa-fw fa-trophy', 'roles' => ['administrador', 'coordinador', 'maestro', 'profesor']],
            ['text' => 'Punto de Venta (POS)', 'url' => 'pos', 'icon' => 'fas fa-fw fa-cash-register', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Control de Colegiaturas', 'url' => 'colegiaturas', 'icon' => 'fas fa-fw fa-money-bill-wave', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Cobros Especiales', 'url' => 'adeudos/especial', 'icon' => 'fas fa-fw fa-file-invoice', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Ciclos Masivo', 'url' => 'ciclos', 'icon' => 'fas fa-fw fa-calendar-alt', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Recargos Manuales', 'url' => 'adeudos/recargos-manual', 'icon' => 'fas fa-fw fa-exclamation-triangle', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Estados de Cuenta', 'url' => 'cartera', 'icon' => 'fas fa-fw fa-search-dollar', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Clases Extracurriculares', 'url' => 'extracurriculares', 'icon' => 'fas fa-fw fa-running', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Reporte de Cobranza', 'url' => 'reportes/cobranza', 'icon' => 'fas fa-fw fa-hand-holding-usd', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Pendientes por Mes', 'url' => 'reportes/pendientes-mes', 'icon' => 'fas fa-fw fa-calendar-times', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Historial Colegiaturas', 'url' => 'reportes/historial-colegiaturas', 'icon' => 'fas fa-fw fa-history', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Adeudos Especiales', 'url' => 'reportes/adeudos-especiales', 'icon' => 'fas fa-fw fa-star', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Exportar Saldos (Excel)', 'url' => 'reportes/exportar-saldos', 'icon' => 'fas fa-fw fa-file-excel', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Lista de Ventas', 'url' => 'contabilidad/ventas', 'icon' => 'fas fa-fw fa-list', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Ventas Canceladas', 'url' => 'contabilidad/ventas-canceladas', 'icon' => 'fas fa-fw fa-ban', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Ventas por Fecha', 'url' => 'contabilidad/ventas-por-fecha', 'icon' => 'fas fa-fw fa-calendar-day', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Ventas por Producto', 'url' => 'contabilidad/ventas-producto', 'icon' => 'fas fa-fw fa-boxes', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Reporte por Producto', 'url' => 'contabilidad/reporte-producto', 'icon' => 'fas fa-fw fa-boxes', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Efectivo en Cajas', 'url' => 'contabilidad/efectivo-cajas', 'icon' => 'fas fa-fw fa-cash-register', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Discrepancias', 'url' => 'contabilidad/discrepancias', 'icon' => 'fas fa-fw fa-exclamation-circle', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Gastos', 'url' => 'contabilidad/gastos', 'icon' => 'fas fa-fw fa-file-invoice', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Corte de Caja', 'url' => 'contabilidad/corte-caja', 'icon' => 'fas fa-fw fa-file-invoice-dollar', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Catálogo de Productos', 'url' => 'productos', 'icon' => 'fas fa-fw fa-box', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Importar Pagos (Excel)', 'url' => 'importar-pagos', 'icon' => 'fas fa-fw fa-file-import', 'roles' => ['administrador', 'socio', 'cajero']],
            ['text' => 'Mis Hijos', 'url' => 'portal-padre/dashboard', 'icon' => 'fas fa-fw fa-child', 'roles' => ['padre']],
        ];

        foreach ($menus as $m) {
            $roles = $m['roles'];
            unset($m['roles']);
            
            $menu = Menu::where('url', $m['url'])->first();
            if (!$menu) {
                $menu = Menu::create($m);
            } else {
                $menu->update([
                    'text' => $m['text'],
                    'icon' => $m['icon']
                ]);
            }

            $roleIds = [];
            foreach ($roles as $rName) {
                // Soporte robusto para 'maestro' y 'profesor'
                $namesToCheck = [$rName];
                if ($rName === 'maestro') {
                    $namesToCheck[] = 'profesor';
                } elseif ($rName === 'profesor') {
                    $namesToCheck[] = 'maestro';
                }

                $role = Role::whereIn('name', $namesToCheck)->first();
                if ($role) {
                    $roleIds[] = $role->id;
                }
            }
            if (!empty($roleIds)) {
                $menu->roles()->sync($roleIds);
            }
        }
    }
}
