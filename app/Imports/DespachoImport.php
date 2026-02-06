<?php

namespace App\Imports;

use App\Models\Despacho;
use App\Models\DespachoProducto;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DespachoImport implements ToCollection
{
    protected $usuarioId;

    public function __construct($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }

    public function collection(Collection $rows)
    {
        try {
            // Extraer datos de la cabecera del Excel
            $fechaExpedicion = $this->parseFecha($rows[5][2] ?? null);
            $placaRemolque = $rows[5][5] ?? 'SXT 135';
            $conductor = $rows[7][2] ?? 'Sin conductor';
            $destinoGeneral = $rows[9][2] ?? 'TEMP1';

            // Crear el despacho (cabecera)
            $despacho = Despacho::create([
                'conductor' => $conductor,
                'placa_remolque' => $placaRemolque,
                'destino_general' => $destinoGeneral,
                'fecha_expedicion' => $fechaExpedicion,
                'lenguas' => 0,
                'archivo_original' => request()->file('excel_file')->getClientOriginalName(),
                'usuario_id' => $this->usuarioId,
            ]);

            $contadorLenguas = 0;

            // Procesar productos (a partir de la fila 14)
            for ($i = 14; $i < $rows->count(); $i++) {
                $row = $rows[$i];

                if (empty($row[0])) continue;

                $codigoCompleto = trim($row[0] ?? '');
                if (empty($codigoCompleto)) continue;

                // Extraer solo el código (sin descripción)
                $partes = explode(' ', $codigoCompleto, 2);
                $codigo = $partes[0] ?? $codigoCompleto;

                // ✅ SOLO PROCESAR SI TERMINA EN -1001
                if (!str_ends_with($codigo, '-1001')) {
                    continue; // Ignorar -1002 y cualquier otro sufijo
                }

                // Obtener código base: 2601-11413-1001 -> 2601-11413
                $codigoBase = $this->obtenerCodigoBase($codigo);

                // Convertir a código lengua: 2601-11413 -> 2601-11413-6000
                $codigoLengua = $codigoBase . '-6000';

                // Parsear valores
                $decomisos = trim($row[4] ?? '');
                $destinoEspecifico = trim($row[5] ?? '');
                $fechaBeneficio = $this->parseFecha($row[6] ?? null);

                // Crear la lengua
                DespachoProducto::create([
                    'despacho_id' => $despacho->id,
                    'codigo_producto' => $codigoLengua,
                    'descripcion_producto' => 'LENGUA',
                    'peso_frio' => 0,
                    'peso_caliente' => 0,
                    'temperatura' => null,
                    'decomisos' => $decomisos,
                    'destino_especifico' => $destinoEspecifico,
                    'fecha_beneficio' => $fechaBeneficio,
                ]);

                $contadorLenguas++;
            }

            // Actualizar cantidad de lenguas (solo las que vienen de -1001)
            $despacho->update(['lenguas' => $contadorLenguas]);

        } catch (\Exception $e) {
            throw new \Exception('Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener código base del animal (sin sufijo)
     * Ejemplo: 2601-11413-1001 -> 2601-11413
     */
    private function obtenerCodigoBase($codigo)
    {
        // Dividir por guiones
        $partes = explode('-', $codigo);

        // Si tiene 3 partes, tomar las primeras 2
        if (count($partes) >= 3) {
            return $partes[0] . '-' . $partes[1];
        }

        return $codigo;
    }

    /**
     * Parsear fechas en múltiples formatos
     */
    private function parseFecha($fecha)
    {
        if (empty($fecha)) {
            return null;
        }

        try {
            if (is_numeric($fecha)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha)
                );
            }

            if (is_string($fecha)) {
                $formatos = [
                    'd/m/Y H:i:s',
                    'd/m/Y H:i',
                    'd/m/Y',
                    'Y-m-d H:i:s',
                    'Y-m-d',
                    'd-m-Y',
                ];

                foreach ($formatos as $formato) {
                    try {
                        return Carbon::createFromFormat($formato, trim($fecha));
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                return Carbon::parse($fecha);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
