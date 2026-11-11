<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoUnidad extends Model
{
    protected $table = 'codigos_unidad';

    protected $guarded = [];

    /** Texto corto para el desplegable: sin "(Acción de Fiscalización: ...)" */
    public static function etiqueta(string $descripcion): string
    {
        $corto = preg_replace('/\s*\(Acci[oó]n(es)? de Fiscalizaci[oó]n:.*\)\s*$/u', '', $descripcion);

        return trim($corto ?: $descripcion);
    }
}
