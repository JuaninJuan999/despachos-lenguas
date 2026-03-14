<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserActivityReportController extends Controller
{
    private const MINUTES_PER_RECORD = 8;

    public function index(Request $request): View
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Acceso denegado. Solo administradores.');
        }

        $defaultDesde = now()->subDays(6)->toDateString();
        $defaultHasta = now()->toDateString();

        $desde = $request->input('desde', $defaultDesde);
        $hasta = $request->input('hasta', $defaultHasta);

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $selectedUserId = $request->integer('user_id');

        $query = Despacho::query()
            ->with('creator:id,name,username')
            ->whereNotNull('created_by')
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_by')
            ->orderBy('created_at');

        if ($selectedUserId) {
            $query->where('created_by', $selectedUserId);
        }

        $records = $query->get();

        $minutesByUser = [];
        $minutesByDay = [];
        $recordsByUser = [];

        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $date) {
            $minutesByDay[$date->toDateString()] = 0;
        }

        foreach ($records as $record) {
            $userId = $record->created_by;
            $dayKey = $record->created_at->toDateString();

            if (! isset($minutesByUser[$userId])) {
                $minutesByUser[$userId] = [
                    'label' => $record->creator?->name ?? ('Usuario #' . $userId),
                    'minutes' => 0,
                ];
                $recordsByUser[$userId] = 0;
            }

            $minutesByUser[$userId]['minutes'] += self::MINUTES_PER_RECORD;
            $recordsByUser[$userId]++;

            if (isset($minutesByDay[$dayKey])) {
                $minutesByDay[$dayKey] += self::MINUTES_PER_RECORD;
            }
        }

        usort($minutesByUser, fn ($a, $b) => $b['minutes'] <=> $a['minutes']);

        $chartUserLabels = array_map(fn ($item) => $item['label'], $minutesByUser);
        $chartUserMinutes = array_map(fn ($item) => $item['minutes'], $minutesByUser);

        $chartDailyLabels = array_map(
            fn ($day) => Carbon::parse($day)->format('d/m'),
            array_keys($minutesByDay)
        );
        $chartDailyMinutes = array_values($minutesByDay);

        $totalMinutes = array_sum($chartUserMinutes);

        $users = User::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('reports.user-activity', [
            'users' => $users,
            'selectedUserId' => $selectedUserId,
            'desde' => $start->toDateString(),
            'hasta' => $end->toDateString(),
            'recordsCount' => $records->count(),
            'totalMinutes' => $totalMinutes,
            'minutesPerRecord' => self::MINUTES_PER_RECORD,
            'chartUserLabels' => $chartUserLabels,
            'chartUserMinutes' => $chartUserMinutes,
            'chartDailyLabels' => $chartDailyLabels,
            'chartDailyMinutes' => $chartDailyMinutes,
        ]);
    }
}
