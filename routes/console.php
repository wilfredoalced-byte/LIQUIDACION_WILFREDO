<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('agentes:importar {archivo}', function (string $archivo) {
    if (! is_file($archivo)) {
        $this->error("No existe el archivo: {$archivo}");
        return 1;
    }

    $hoja = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo)->getActiveSheet();
    $filas = $hoja->toArray(null, true, false, false);

    $normalizar = function ($texto) {
        $texto = (string) $texto;
        $texto = strtr($texto, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U']);
        return trim(preg_replace('/\s+/', ' ', strtoupper($texto)));
    };

    $cabecera = null;
    foreach ($filas as $i => $fila) {
        if (in_array('CODIGO OSINERGMIN', array_map($normalizar, $fila), true)) {
            $cabecera = $i;
            break;
        }
    }

    if ($cabecera === null) {
        $this->error('No se encontró la columna CODIGO OSINERGMIN en el Excel.');
        return 1;
    }

    $col = [];
    foreach ($filas[$cabecera] as $j => $titulo) {
        $col[$normalizar($titulo)] = $j;
    }

    $valor = fn ($fila, $nombre) => isset($col[$nombre]) ? trim((string) ($fila[$col[$nombre]] ?? '')) : '';

    $total = 0;
    foreach (array_slice($filas, $cabecera + 1) as $fila) {
        $codigo = $valor($fila, 'CODIGO OSINERGMIN');
        if ($codigo === '') {
            continue;
        }

        $actividad = $valor($fila, 'ACTIVIDAD');
        $codigoActividad = null;
        $nombreActividad = $actividad;
        if (preg_match('/^\s*(\d+)\s*-\s*(.+)$/u', $actividad, $m)) {
            $codigoActividad = $m[1];
            $nombreActividad = trim($m[2]);
        }

        $fecha = null;
        if (isset($col['FECHA DE EMISION'])) {
            $f = $fila[$col['FECHA DE EMISION']] ?? null;
            if (is_numeric($f)) {
                $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($f)->format('Y-m-d');
            } elseif (is_string($f) && preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})#', $f, $d)) {
                $fecha = sprintf('%04d-%02d-%02d', $d[3], $d[2], $d[1]);
            }
        }

        \App\Models\Agente::query()->updateOrCreate(
            ['codigo_osinergmin' => $codigo],
            [
                'codigo_actividad' => $codigoActividad,
                'actividad' => $nombreActividad,
                'nro_registro' => $valor($fila, 'NRO. DE REGISTRO') ?: null,
                'fecha_emision' => $fecha,
                'ruc' => $valor($fila, 'RUC') ?: null,
                'razon_social' => $valor($fila, 'RAZON SOCIAL'),
                'direccion' => $valor($fila, 'DIRECCION') ?: null,
                'ubigeo' => $valor($fila, 'UBIGEO') ?: null,
                'placa' => $valor($fila, 'PLACA PRINCIPAL') ?: null,
            ],
        );
        $total++;
    }

    $this->info("Agentes importados: {$total}");
    return 0;
})->purpose('Importa la relación de agentes de fiscalización desde un Excel');

Artisan::command('unidades:importar {archivo}', function (string $archivo) {
    if (! is_file($archivo)) {
        $this->error("No existe el archivo: {$archivo}");
        return 1;
    }

    $filas = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivo)->getActiveSheet()->toArray(null, true, false, false);
    $limpiar = fn ($v) => trim(preg_replace('/\s+/u', ' ', (string) $v));
    $plazo = function ($v) use ($limpiar) {
        $v = $limpiar($v);
        return $v === '' ? null : $v;
    };

    $accion = null;
    $descripcion = null;
    $orden = 0;
    $registros = [];

    foreach ($filas as $fila) {
        $a = $limpiar($fila[0] ?? '');
        $b = $limpiar($fila[1] ?? '');

        if ($a !== '' && ! str_starts_with($a, '*') && stripos($a, 'Acci') !== 0 && stripos($a, 'Cronograma') !== 0 && stripos($a, 'Si el intructivo') !== 0) {
            $accion = $a;
        }
        if (preg_match('/^\d+\.\d+\s/u', $b)) {
            $descripcion = rtrim($b, '. ');
        } elseif ($b !== '') {
            continue;
        }
        if ($descripcion === null || ! preg_match('/^(\d+\.\d+)\s/u', $descripcion, $m)) {
            continue;
        }
        if ($b === '' && $limpiar($fila[2] ?? '') === '') {
            continue;
        }

        $registros[] = [
            'orden' => ++$orden,
            'codigo' => $m[1],
            'descripcion' => $descripcion,
            'accion' => $accion,
            'asignacion' => $plazo($fila[2] ?? null),
            'plazo_general_con_visita' => $plazo($fila[3] ?? null),
            'plazo_general_sin_visita' => $plazo($fila[4] ?? null),
            'plazo_cl_glp_con_visita' => $plazo($fila[5] ?? null),
            'plazo_cl_glp_sin_visita' => $plazo($fila[6] ?? null),
            'plazo_gnv_con_visita' => $plazo($fila[7] ?? null),
            'plazo_gnv_sin_visita' => $plazo($fila[8] ?? null),
            'plazo_mayores_con_visita' => $plazo($fila[9] ?? null),
            'plazo_mayores_sin_visita' => $plazo($fila[10] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    if (! $registros) {
        $this->error('No se encontraron códigos de unidad (filas que empiecen con 23.1, 24.1, etc.).');
        return 1;
    }

    \Illuminate\Support\Facades\DB::table('codigos_unidad')->delete();
    \Illuminate\Support\Facades\DB::table('codigos_unidad')->insert($registros);

    $codigos = collect($registros)->pluck('codigo')->unique()->count();
    $this->info("Filas importadas: " . count($registros) . " | Códigos de unidad distintos: {$codigos}");
    return 0;
})->purpose('Importa los códigos de unidad desde el Excel de plazos de atención');
