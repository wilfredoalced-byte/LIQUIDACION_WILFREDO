<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agente extends Model
{
    protected $table = 'agentes';

    protected $fillable = [
        'codigo_osinergmin',
        'codigo_actividad',
        'actividad',
        'nro_registro',
        'fecha_emision',
        'ruc',
        'razon_social',
        'direccion',
        'ubigeo',
        'placa',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
        ];
    }
}
