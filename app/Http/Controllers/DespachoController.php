<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DespachoController extends Controller
{
    // Muestra el formulario
    public function showImport()
    {
        return view('import');
    }

    // Procesa el Excel subido
    public function importExcel(Request $request)
    {
        // Validar que sea Excel
        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx|max:2048'
        ]);

        $file = $request->file('excel_file');
        
        // Guardar nombre del archivo
        Log::info('✅ Archivo recibido: ' . $file->getClientOriginalName());
        
        // 🆕 PRONTO: aquí leeremos tu Excel
        
        return back()->with('success', 
            '✅ Archivo recibido: ' . $file->getClientOriginalName() . 
            '<br>📊 Próximamente procesaremos conductor, destinos y kg'
        );
    }
}
