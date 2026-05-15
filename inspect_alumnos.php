<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Alumno;
use App\Models\Adeudo;

$alumnos = Alumno::where('nombre', 'like', 'Alumno%')->get();
foreach ($alumnos as $a) {
    echo "--- {$a->nombre} (ID: {$a->id}) ---\n";
    $adeudos = Adeudo::where('alumno_id', $a->id)->orderBy('periodo')->get();
    foreach ($adeudos as $adeudo) {
        echo "{$adeudo->periodo} | {$adeudo->status} | Base: {$adeudo->monto_base} | Actual: {$adeudo->monto_actual}\n";
    }
    echo "\n";
}
