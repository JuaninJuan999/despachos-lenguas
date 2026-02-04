<!DOCTYPE html>
<html>
<head>
    <title>Importar Despacho de Lenguas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 20px; }
        input[type=file] { width: 100%; padding: 10px; border: 1px solid #ddd; }
        button { background: #28a745; color: white; padding: 12px 30px; border: none; cursor: pointer; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <h1>📊 Subir Excel Trazabilidad</h1>
    <p>Sube tu archivo <strong>entrega-de-productos-y-subproductos.xls</strong></p>
    
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb;">
            {{ session('success') }}
        </div>
    @endif
    
    <form action="{{ route('importar.excel') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>Archivo Excel:</label>
            <input type="file" name="excel_file" accept=".xls,.xlsx" required>
        </div>
        <button type="submit">🚀 Procesar Despacho de Lenguas</button>
    </form>
</body>
</html>
