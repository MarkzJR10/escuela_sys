<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Menu;

$menus = Menu::with('roles')->get();
echo "Menus en DB:\n";
foreach ($menus as $menu) {
    $roles = $menu->roles->pluck('name')->implode(', ');
    echo "- ID: {$menu->id}, Text: {$menu->text}, URL: {$menu->url}, Roles: [{$roles}]\n";
}
