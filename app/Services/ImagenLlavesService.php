<?php

namespace App\Services;

use App\Models\Despacho;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImagenLlavesService
{
    protected ImageManager $imageManager;
    
    // Colores profesionales
    private const COLOR_HEADER = '#1e40af';        // Azul profesional
    private const COLOR_HEADER_TEXT = '#ffffff';   // Blanco
    private const COLOR_TABLA_HEADER = '#3b82f6';  // Azul tabla
    private const COLOR_FILA_PAR = '#f3f4f6';      // Gris claro
    private const COLOR_FILA_IMPAR = '#ffffff';    // Blanco
    private const COLOR_BORDE = '#9ca3af';         // Gris borde
    private const COLOR_TEXTO = '#1f2937';         // Gris oscuro
    
    // Dimensiones (MÁS GRANDES)
private const ANCHO_IMAGEN = 1600;
private const ALTO_HEADER = 220;
private const ALTO_TABLA_HEADER = 90;
private const ALTO_FILA = 100;
private const PADDING = 50;

// Anchos de columnas
private const ANCHO_COL_DESTINO = 350;
private const ANCHO_COL_DIRECCION = 1150;


    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Genera una imagen PNG con los destinos únicos del despacho
     */
    public function generarImagenLlaves(Despacho $despacho): string
    {
        // Obtener destinos únicos y parsearlos
        $destinosProcesados = $despacho->productos()
            ->select('destino_especifico')
            ->distinct()
            ->whereNotNull('destino_especifico')
            ->pluck('destino_especifico')
            ->map(function ($destino) {
                return $this->parsearDestino($destino);
            })
            ->filter() // Eliminar nulos
            ->unique('codigo') // Únicos por código
            ->sortBy('codigo')
            ->values();

        if ($destinosProcesados->isEmpty()) {
            throw new \Exception('No hay destinos para generar la imagen de llaves.');
        }

        // Calcular altura total
        $altoTotal = self::ALTO_HEADER + self::ALTO_TABLA_HEADER + 
                     (count($destinosProcesados) * self::ALTO_FILA) + 
                     (self::PADDING * 2);

        // Crear imagen
        $imagen = $this->imageManager->create(self::ANCHO_IMAGEN, $altoTotal)
            ->fill('#ffffff');

        // Dibujar header
        $this->dibujarHeader($imagen, $despacho, count($destinosProcesados));

        // Dibujar tabla
        $this->dibujarTabla($imagen, $destinosProcesados);

        // Guardar imagen
        $nombreArchivo = 'llaves-despacho-' . $despacho->id . '-' . now()->timestamp . '.png';
        $rutaCompleta = storage_path('app/public/despachos/' . $nombreArchivo);

        if (!file_exists(dirname($rutaCompleta))) {
            mkdir(dirname($rutaCompleta), 0755, true);
        }

        $imagen->toPng()->save($rutaCompleta);

        return 'despachos/' . $nombreArchivo;
    }

    /**
     * Parsear destino en código y dirección
     */
    private function parsearDestino(string $destino): ?array
    {
        // Dividir por "/"
        $partes = array_map('trim', explode('/', $destino));
        
        // Quitar las primeras 2 partes (TEMP1 y zona)
        if (count($partes) > 2) {
            array_shift($partes); // Quita TEMP1
            array_shift($partes); // Quita zona
        }

        if (empty($partes)) {
            return null;
        }

        // Primera parte es el código, resto es dirección
        $codigo = array_shift($partes);
        $direccion = implode(' / ', $partes);

        return [
            'codigo' => strtoupper($codigo),
            'direccion' => $direccion ?: 'N/A'
        ];
    }

    /**
     * Dibuja el header
     */
    private function dibujarHeader($imagen, Despacho $despacho, int $totalDestinos): void
    {
        // Fondo azul
        $imagen->drawRectangle(0, 0, function ($rectangle) {
            $rectangle->size(self::ANCHO_IMAGEN, self::ALTO_HEADER);
            $rectangle->background(self::COLOR_HEADER);
        });

        // Título
        $imagen->text('LLAVES DE DISTRIBUCIÓN', self::ANCHO_IMAGEN / 2, 40, function ($font) {
            $font->size(52);
            $font->color(self::COLOR_HEADER_TEXT);
            $font->align('center');
            $font->valign('top');
        });

        // Línea separadora
        $imagen->drawRectangle(self::PADDING, 95, function ($rectangle) {
            $rectangle->size(self::ANCHO_IMAGEN - (self::PADDING * 2), 2);
            $rectangle->background('rgba(255, 255, 255, 0.3)');
        });

        // Info despacho
        $info = sprintf(
            'Despacho #%d  |  Conductor: %s  |  Placa: %s  |  Fecha: %s  |  Destinos: %d',
            $despacho->id,
            $despacho->conductor ?? 'N/A',
            $despacho->placa_remolque ?? 'N/A',
            $despacho->fecha_expedicion ? $despacho->fecha_expedicion->format('d/m/Y') : 'N/A',
            $totalDestinos
        );

        $imagen->text($info, self::ANCHO_IMAGEN / 2, 115, function ($font) {
            $font->size(26);
            $font->color(self::COLOR_HEADER_TEXT);
            $font->align('center');
            $font->valign('top');
        });

        // Total lenguas
        if ($despacho->lenguas) {
            $imagen->text(
                'Total Lenguas: ' . number_format($despacho->lenguas),
                self::ANCHO_IMAGEN / 2,
                150,
                function ($font) {
                    $font->size(24);
                    $font->color(self::COLOR_HEADER_TEXT);
                    $font->align('center');
                    $font->valign('top');
                }
            );
        }
    }

    /**
     * Dibuja la tabla con destinos
     */
    private function dibujarTabla($imagen, $destinos): void
    {
        $yTabla = self::ALTO_HEADER + self::PADDING;
        $xInicio = self::PADDING;

        // Header de la tabla
        $this->dibujarHeaderTabla($imagen, $xInicio, $yTabla);

        // Filas de datos
        $yFila = $yTabla + self::ALTO_TABLA_HEADER;

        foreach ($destinos as $index => $destino) {
            $this->dibujarFilaTabla($imagen, $xInicio, $yFila, $destino, $index);
            $yFila += self::ALTO_FILA;
        }
    }

    /**
     * Dibuja el header de la tabla
     */
    private function dibujarHeaderTabla($imagen, int $x, int $y): void
    {
        $anchoTotal = self::ANCHO_COL_DESTINO + self::ANCHO_COL_DIRECCION;

        // Fondo azul
        $imagen->drawRectangle($x, $y, function ($rectangle) use ($anchoTotal) {
            $rectangle->size($anchoTotal, self::ALTO_TABLA_HEADER);
            $rectangle->background(self::COLOR_TABLA_HEADER);
            $rectangle->border(self::COLOR_BORDE, 2);
        });

        // Línea divisoria vertical
        $imagen->drawRectangle($x + self::ANCHO_COL_DESTINO, $y, function ($rectangle) {
            $rectangle->size(2, self::ALTO_TABLA_HEADER);
            $rectangle->background(self::COLOR_BORDE);
        });

        // Texto "Destino"
        $imagen->text(
            'DESTINO',
            $x + (self::ANCHO_COL_DESTINO / 2),
            $y + (self::ALTO_TABLA_HEADER / 2) - 2,
            function ($font) {
                $font->size(28);
                $font->color(self::COLOR_HEADER_TEXT);
                $font->align('center');
                $font->valign('middle');
            }
        );

        // Texto "Dirección"
        $imagen->text(
            'DIRECCIÓN',
            $x + self::ANCHO_COL_DESTINO + (self::ANCHO_COL_DIRECCION / 2),
            $y + (self::ALTO_TABLA_HEADER / 2) - 2,
            function ($font) {
                $font->size(28);
                $font->color(self::COLOR_HEADER_TEXT);
                $font->align('center');
                $font->valign('middle');
            }
        );
    }

    /**
     * Dibuja una fila de la tabla
     */
    private function dibujarFilaTabla($imagen, int $x, int $y, array $destino, int $index): void
    {
        $anchoTotal = self::ANCHO_COL_DESTINO + self::ANCHO_COL_DIRECCION;
        $colorFondo = $index % 2 === 0 ? self::COLOR_FILA_PAR : self::COLOR_FILA_IMPAR;

        // Fondo de la fila
        $imagen->drawRectangle($x, $y, function ($rectangle) use ($anchoTotal, $colorFondo) {
            $rectangle->size($anchoTotal, self::ALTO_FILA);
            $rectangle->background($colorFondo);
            $rectangle->border(self::COLOR_BORDE, 1);
        });

        // Línea divisoria vertical
        $imagen->drawRectangle($x + self::ANCHO_COL_DESTINO, $y, function ($rectangle) {
            $rectangle->size(1, self::ALTO_FILA);
            $rectangle->background(self::COLOR_BORDE);
        });

        // Código de destino (centrado)
        $imagen->text(
            $destino['codigo'],
            $x + (self::ANCHO_COL_DESTINO / 2),
            $y + (self::ALTO_FILA / 2) - 2,
            function ($font) {
                $font->size(26);
                $font->color(self::COLOR_TEXTO);
                $font->align('center');
                $font->valign('middle');
            }
        );

        // Dirección (izquierda con padding)
        $textoDir = strlen($destino['direccion']) > 65 
            ? substr($destino['direccion'], 0, 62) . '...' 
            : $destino['direccion'];

        $imagen->text(
            $textoDir,
            $x + self::ANCHO_COL_DESTINO + 20,
            $y + (self::ALTO_FILA / 2) - 2,
            function ($font) {
                $font->size(24);
                $font->color(self::COLOR_TEXTO);
                $font->align('left');
                $font->valign('middle');
            }
        );
    }

    /**
     * Elimina imágenes antiguas
     */
    public function limpiarImagenesAntiguas(int $despachoId): void
    {
        $patron = storage_path('app/public/despachos/llaves-despacho-' . $despachoId . '-*.png');
        
        foreach (glob($patron) as $archivo) {
            if (file_exists($archivo)) {
                @unlink($archivo);
            }
        }
    }
}
