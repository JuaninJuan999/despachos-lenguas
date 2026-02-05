<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importar Despacho') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {!! session('success') !!}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {!! session('error') !!}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">📋 Instrucciones:</h3>
                        <ul class="list-disc list-inside text-gray-600 space-y-1">
                            <li>Selecciona el archivo Excel de despacho (.xls o .xlsx)</li>
                            <li>El sistema extraerá automáticamente: Conductor, Placa, Destinos y Productos</li>
                            <li>Se creará un registro completo del despacho</li>
                        </ul>
                    </div>

                    <form action="{{ route('despachos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div>
                            <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                                Archivo Excel
                            </label>
                            <input 
                                type="file" 
                                name="excel_file" 
                                id="excel_file" 
                                accept=".xls,.xlsx"
                                required
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500"
                            >
                            <p class="mt-1 text-sm text-gray-500">Formatos permitidos: XLS, XLSX (máx. 2MB)</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <button 
                                type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-200"
                            >
                                📤 Importar Despacho
                            </button>

                            <a 
                                href="{{ route('despachos.index') }}"
                                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition duration-200"
                            >
                                ← Volver al Listado
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
