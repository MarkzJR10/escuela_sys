<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;

$menus = Menu::orderBy('orden')->get();
echo "Menus actuales:\n";
foreach ($menus as $menu) {
    echo "- ID: {$menu->id}, Nombre: {$menu->nombre}, Ruta: {$menu->ruta}, Orden: {$menu->orden}, Seccion: {$menu->seccion}\n";
}
