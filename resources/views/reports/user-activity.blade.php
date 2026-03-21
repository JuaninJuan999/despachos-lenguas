<x-app-layout>
    <x-slot name="title">Actividad de Usuarios</x-slot>

    <div class="p-6 max-w-7xl mx-auto space-y-6">

        {{-- Encabezado --}}
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-amber-500 to-orange-600 bg-clip-text text-transparent">
                Actividad de Usuarios
            </h1>
            <p class="text-gray-600">Tiempo real de sesión registrado al iniciar y cerrar sesión</p>
        </div>

        {{-- Filtros --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4">
            <form method="GET" action="{{ route('reports.user-activity') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="desde" class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Desde</label>
                    <input type="date" id="desde" name="desde" value="{{ $desde }}"
                        class="h-10 rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                </div>
                <div>
                    <label for="hasta" class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ $hasta }}"
                        class="h-10 rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                </div>
                <div class="min-w-[220px]">
                    <label for="user_id" class="block text-xs font-bold uppercase tracking-wide text-gray-600 mb-1">Usuario</label>
                    <select id="user_id" name="user_id"
                        class="h-10 w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                        <option value="">Todos</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" @selected($selectedUserId === $u->id)>
                                {{ $u->name }} ({{ $u->username }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="h-10 px-5 rounded-lg border border-orange-500 bg-orange-300 text-orange-900 font-extrabold tracking-wide hover:bg-orange-400 shadow-md focus:outline-none focus:ring-2 focus:ring-orange-300">
                    Filtrar
                </button>
                <a href="{{ route('reports.user-activity') }}"
                    class="h-10 px-5 rounded-lg border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 inline-flex items-center">
                    Limpiar
                </a>
            </form>
        </div>

        {{-- Tarjetas resumen --}}
        @php
            $horas        = intdiv($totalMinutes, 60);
            $minutos      = $totalMinutes % 60;
            $activasCount = $sessions->filter(fn($s) => $s->is_active)->count();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Tiempo total en sesión</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                    @if($horas > 0)
                        {{ $horas }}h {{ $minutos }}min
                    @else
                        {{ $minutos }} min
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-1">Tiempo real de login a logout</p>
            </div>
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Sesiones registradas</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $sessions->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow border border-gray-100 p-5">
                <p class="text-sm text-gray-500">Sesiones activas ahora</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $activasCount }}</p>
            </div>
        </div>

        {{-- Gráficos --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tiempo por Usuario (min)</h2>
                <div class="h-[300px]">
                    <canvas id="activityByUserChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Tiempo por Día (min)</h2>
                <div class="h-[300px]">
                    <canvas id="activityByDayChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Tabla de sesiones --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Historial de Sesiones</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Inicio de sesión</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cierre de sesión</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Duración</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($sessions->sortByDesc('logged_in_at') as $session)
                            @php
                                $durMin = $session->duration_minutes;
                                $durH   = intdiv($durMin, 60);
                                $durM   = $durMin % 60;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $session->user?->name ?? 'Desconocido' }}
                                    <span class="text-xs text-gray-400 block">{{ $session->user?->username }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $session->logged_in_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    @if($session->logged_out_at)
                                        {{ $session->logged_out_at->format('d/m/Y H:i:s') }}
                                    @elseif($session->is_active)
                                        <span class="text-green-600 font-semibold">Activa</span>
                                    @else
                                        <span class="text-gray-400 italic">Sin logout registrado</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($durMin > 0)
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                            @if($durH > 0)
                                                {{ $durH }}h {{ $durM }}m
                                            @else
                                                {{ $durMin }}m
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs font-mono">{{ $session->ip_address }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($session->is_active)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
                                            Activa
                                        </span>
                                    @elseif($session->logged_out_at)
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Cerrada</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Expirada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    No hay sesiones registradas en este período
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const userLabels   = @json($chartUserLabels);
        const userMinutes  = @json($chartUserMinutes);
        const dailyLabels  = @json($chartDailyLabels);
        const dailyMinutes = @json($chartDailyMinutes);

        new Chart(document.getElementById('activityByUserChart'), {
            type: 'bar',
            data: {
                labels: userLabels,
                datasets: [{ label: 'Minutos', data: userMinutes, backgroundColor: '#f59e0b', borderRadius: 8 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });

        new Chart(document.getElementById('activityByDayChart'), {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Minutos por día',
                    data: dailyMinutes,
                    borderColor: '#ea580c',
                    backgroundColor: 'rgba(234,88,12,0.15)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    </script>

</x-app-layout>

