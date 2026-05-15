<?php

namespace App\Http\Controllers;

use App\Models\PeriodoControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PeriodoControlController extends Controller
{
    public function index()
    {
        $periodos = PeriodoControl::orderBy('trimestre')->get();
        return view('periodos.index', compact('periodos'));
    }

    public function toggle(Request $request, PeriodoControl $periodoControl)
    {
        Log::info("Toggling period ID: " . $periodoControl->id . " from " . ($periodoControl->activo ? 'ON' : 'OFF'));
        $periodoControl->activo = !$periodoControl->activo;
        $periodoControl->save();
        Log::info("New state for ID " . $periodoControl->id . ": " . ($periodoControl->activo ? 'ON' : 'OFF'));

        return response()->json([
            'success' => true,
            'activo' => (bool)$periodoControl->activo
        ]);
    }
}
