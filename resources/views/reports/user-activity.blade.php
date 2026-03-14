<x-app-layout>
    <x-slot name="title">Actividad de Usuarios</x-slot>

    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-amber-500 to-orange-600 bg-clip-text text-transparent">
                    Actividad de Usuarios
                </h1>
                <p class="text-gray-600">Visualiza el tiempo estimado de actividad segun los registros creados en el aplicativo</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <form method="GET" action="{{ route('reports.user-activity') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="desde" class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Desde</label>
                    <input type="date" id="desde" name="desde" value="{{ $desde }}" class="h-10 rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                </div>

                <div>
                    <label for="hasta" class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ $hasta }}" class="h-10 rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                </div>

                <div class="min-w-[220px]">
                    <label for="user_id" class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Usuario</label>
                    <select id="user_id" name="user_id" class="h-10 w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Todos</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($selectedUserId === $user->id)>
                                {{ $user->name }} ({{ $user->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="h-10 px-5 rounded-lg border border-orange-500 bg-orange-300 text-orange-900 font-extrabold tracking-wide hover:bg-orange-400 shadow-md focus:outline-none focus:ring-2 focus:ring-orange-300">
                    Filtrar
                </button>
                <a href="{{ route('reports.user-activity') }}" class="h-10 px-5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 inline-flex items-center">
                    Limpiar
                </a>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Tiempo total estimado</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalMinutes) }} min</p>
                <p class="text-xs text-gray-500 mt-2">Calculo basado en {{ $minutesPerRecord }} min por registro creado</p>
            </div>
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Registros realizados</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($recordsCount) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tiempo Estimado por Usuario (min)</h2>
                <div class="h-[340px]">
                    <canvas id="activityByUserChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tiempo Estimado Diario (min)</h2>
                <div class="h-[340px]">
                    <canvas id="activityByDayChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const userLabels = @json($chartUserLabels);
        const userMinutes = @json($chartUserMinutes);
        const dailyLabels = @json($chartDailyLabels);
        const dailyMinutes = @json($chartDailyMinutes);

        new Chart(document.getElementById('activityByUserChart'), {
            type: 'bar',
            data: {
                labels: userLabels,
                datasets: [{
                    label: 'Minutos',
                    data: userMinutes,
                    backgroundColor: '#f59e0b',
                    borderRadius: 8,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });

        new Chart(document.getElementById('activityByDayChart'), {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Minutos por dia',
                    data: dailyMinutes,
                    borderColor: '#ea580c',
                    backgroundColor: 'rgba(234, 88, 12, 0.15)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                        },
                    },
                },
            },
        });
    </script>
</x-app-layout>
