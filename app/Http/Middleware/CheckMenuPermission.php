<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Administrador tiene acceso total
        if ($user->hasRole('administrador')) {
            return $next($request);
        }

        // Obtener path actual sin barra inicial
        $path = ltrim($request->getPathInfo(), '/');

        // Buscar coincidencia exacta o por inicio de ruta en la tabla menus
        $menu = Menu::with('roles')->get()->first(function ($m) use ($path) {
            $menuUrl = trim($m->url, '/');
            if (empty($menuUrl)) return false;
            return $path === $menuUrl || str_starts_with($path, $menuUrl . '/');
        });

        if ($menu) {
            $userRoles = $user->roles->pluck('name')->toArray();
            if (in_array('profesor', $userRoles) && !in_array('maestro', $userRoles)) {
                $userRoles[] = 'maestro';
            }
            if (in_array('maestro', $userRoles) && !in_array('profesor', $userRoles)) {
                $userRoles[] = 'profesor';
            }

            $menuRoles = $menu->roles->pluck('name')->toArray();
            if (in_array('profesor', $menuRoles) && !in_array('maestro', $menuRoles)) {
                $menuRoles[] = 'maestro';
            }
            if (in_array('maestro', $menuRoles) && !in_array('profesor', $menuRoles)) {
                $menuRoles[] = 'profesor';
            }

            if (count(array_intersect($userRoles, $menuRoles)) === 0) {
                abort(403, 'Lo sentimos no cuentas con los permisos para este menu, contactar a soporte');
            }
        }

        return $next($request);
    }
}
