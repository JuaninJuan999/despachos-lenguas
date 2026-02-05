<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Imports\DespachoImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Despacho;
use Barryvdh\DomPDF\Facade\Pdf;

class DespachoController extends Controller
{
    // Muestra el formulario de importación
    public function showImport()
    {
        return view('despachos.import');
    }

    // Procesa el Excel subido
    public function importExcel(Request $request)
    {
        // Validar que sea Excel
        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx|max:2048'
        ]);

        try {
            $file = $request->file('excel_file');
            
            // Importar el Excel
            Excel::import(new DespachoImport(auth()->id()), $file);
            
            return redirect()->route('despachos.index')
                ->with('success', '✅ Despacho importado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al importar Excel: ' . $e->getMessage());
            
            return back()->with('error', '❌ Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    // Lista todos los despachos
    public function index()
    {
        $despachos = Despacho::with('usuario')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('despachos.index', compact('despachos'));
    }

    // Muestra el detalle de un despacho
    public function show($id)
    {
        $despacho = Despacho::with('productos')->findOrFail($id);
        
        return view('despachos.show', compact('despacho'));
    }

    // Generar PDF del despacho

public function generatePDF(Despacho $despacho)
{
    // Verificar que el usuario tenga permiso (es el dueño del despacho)
    if ($despacho->usuario_id !== auth()->id()) {
        abort(403, 'No tienes permiso para ver este despacho');
    }

    // Cargar los productos del despacho
    $despacho->load('productos');

    // Generar el PDF
    $pdf = Pdf::loadView('despachos.pdf.despacho', compact('despacho'))
        ->setPaper('a4', 'landscape'); // Horizontal para mejor visualización

    // Descargar el PDF
    return $pdf->download('despacho-' . $despacho->id . '.pdf');
}
}
