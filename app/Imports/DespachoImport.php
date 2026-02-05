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
                'lenguas' => 0, // Se calculará después
                'archivo_original' => request()->file('excel_file')->getClientOriginalName(),
                'usuario_id' => $this->usuarioId,
            ]);

            $contadorLenguas = 0;

            // Procesar productos (a partir de la fila 14)
            for ($i = 14; $i < $rows->count(); $i++) {
                $row = $rows[$i];

                // Verificar que tenga datos
                if (empty($row[0])) continue;

                $codigoCompleto = trim($row[0] ?? '');
                if (empty($codigoCompleto)) continue;

                // Extraer código y descripción
                $partes = explode(' ', $codigoCompleto, 2);
                $codigoProducto = $partes[0] ?? $codigoCompleto;
                $descripcionProducto = $partes[1] ?? '';

                // Parsear valores numéricos de forma segura
                $pesoFrio = $this->parseDecimal($row[1] ?? 0);
                $pesoCaliente = $this->parseDecimal($row[2] ?? 0);
                $temperatura = $this->parseDecimal($row[3] ?? null);
                
                $decomisos = trim($row[4] ?? '');
                $destinoEspecifico = trim($row[5] ?? '');
                $fechaBeneficio = $this->parseFecha($row[6] ?? null);

                // Crear producto del despacho
                DespachoProducto::create([
                    'despacho_id' => $despacho->id,
                    'codigo_producto' => $codigoProducto,
                    'descripcion_producto' => $descripcionProducto,
                    'peso_frio' => $pesoFrio,
                    'peso_caliente' => $pesoCaliente,
                    'temperatura' => $temperatura,
                    'decomisos' => $decomisos,
                    'destino_especifico' => $destinoEspecifico,
                    'fecha_beneficio' => $fechaBeneficio,
                ]);

                $contadorLenguas++;
            }

            // Actualizar cantidad de lenguas
            $despacho->update(['lenguas' => $contadorLenguas]);

        } catch (\Exception $e) {
            // Propagar error para mostrarlo en la interfaz
            throw new \Exception('Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Parsear un valor decimal de forma segura
     * Maneja: "0,0", "0.0", "0", null, etc.
     */
    private function parseDecimal($valor)
    {
        // Si es null o vacío, retornar null
        if ($valor === null || $valor === '') {
            return null;
        }

        // Convertir a string para procesamiento
        $valor = (string)$valor;

        // Reemplazar coma por punto (formato decimal)
        $valor = str_replace(',', '.', $valor);

        // Limpiar espacios
        $valor = trim($valor);

        // Convertir a float
        $resultado = floatval($valor);

        // Si el resultado es 0 y el valor original no era "0", retornar null
        if ($resultado == 0 && !in_array($valor, ['0', '0.0', '0,0'])) {
            return null;
        }

        return $resultado;
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
            // Si es un número (timestamp de Excel)
            if (is_numeric($fecha)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fecha)
                );
            }

            // Si es string, intentar parsear
            if (is_string($fecha)) {
                // Formatos comunes
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

                // Si ningún formato funcionó, intentar con Carbon::parse
                return Carbon::parse($fecha);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
