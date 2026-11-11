<?php

namespace App\Http\Controllers;

use App\Models\Agente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgenteController extends Controller
{
    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $agentes = Agente::query()
            ->where(function ($query) use ($q) {
                $query->where('codigo_osinergmin', 'like', $q.'%')
                    ->orWhere('ruc', 'like', $q.'%')
                    ->orWhere('razon_social', 'like', '%'.$q.'%');
            })
            ->orderByRaw('CASE WHEN codigo_osinergmin = ? THEN 0 WHEN codigo_osinergmin LIKE ? THEN 1 ELSE 2 END', [$q, $q.'%'])
            ->orderBy('razon_social')
            ->limit(15)
            ->get();

        return response()->json($agentes->map(fn (Agente $a) => [
            'codigo_osinergmin' => (string) $a->codigo_osinergmin,
            'codigo_actividad' => (string) $a->codigo_actividad,
            'actividad' => $a->actividad,
            'nro_registro' => $a->nro_registro,
            'fecha_emision' => $a->fecha_emision?->format('d/m/Y'),
            'ruc' => (string) $a->ruc,
            'razon_social' => $a->razon_social,
            'direccion' => $a->direccion,
            'ubigeo' => $a->ubigeo,
            'placa' => $a->placa,
        ])->values());
    }
}
