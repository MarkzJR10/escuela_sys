<?php

namespace App\Http\Controllers;

use App\Models\BitacoraEliminacionAdeudo;
use App\Models\BitacoraStock;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index()
    {
        $bitacorasAdeudos = BitacoraEliminacionAdeudo::with(['usuario', 'alumno'])->orderBy('id', 'desc')->get();
        $bitacorasStock = BitacoraStock::with(['producto', 'usuario'])->orderBy('id', 'desc')->get();

        return view('admin.bitacora.index', compact('bitacorasAdeudos', 'bitacorasStock'));
    }
}
