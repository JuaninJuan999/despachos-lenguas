<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserActivityReportController extends Controller
{
    public function index(Request $request): View
    {
        $defaultDesde = now()->subDays(6)->toDateString();
        $defaultHasta = now()->toDateString();

        $desde = $request->input('desde', $defaultDesde);
        $hasta = $request->input('hasta', $defaultHasta);

        $start = Carbon::parse($desde)->startOfDay();
        $end   = Carbon::parse($hasta)->endOfDay();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $selectedUserId = $request->integer('user_id');

        // Obtener sesiones dentro del rango (por fecha de login)
        $query = UserActivity::with('user:id,name,username')
            ->whereBetween('logged_in_at', [$start, $end])
            ->orderBy('user_id')
            ->orderBy('logged_in_at');

        if ($selectedUserId) {
            $query->where('user_id', $selectedUserId);
        }

        $sessions = $query->get();

        // ── Acumuladores ─────────────────────────────────────────
        $minutesByUser = [];
        $minutesByDay  = [];
        $sessionsByUser = [];

        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $date) {
            $minutesByDay[$date->toDateString()] = 0;
        }

        foreach ($sessions as $session) {
            $userId  = $session->user_id;
            $dayKey  = $session->logged_in_at->toDateString();
            $minutes = $session->duration_minutes;

            if (! isset($minutesByUser[$userId])) {
                $minutesByUser[$userId] = [
                    'label'   => $session->user?->name ?? ('Usuario #' . $userId),
                    'minutes' => 0,
                ];
                $sessionsByUser[$userId] = 0;
            }

            $minutesByUser[$userId]['minutes'] += $minutes;
            $sessionsByUser[$userId]++;

            if (isset($minutesByDay[$dayKey])) {
                $minutesByDay[$dayKey] += $minutes;
            }
        }

        usort($minutesByUser, fn($a, $b) => $b['minutes'] <=> $a['minutes']);

        $chartUserLabels  = array_map(fn($i) => $i['label'],   $minutesByUser);
        $chartUserMinutes = array_map(fn($i) => $i['minutes'], $minutesByUser);

        $chartDailyLabels  = array_map(
            fn($day) => Carbon::parse($day)->format('d/m'),
            array_keys($minutesByDay)
        );
        $chartDailyMinutes = array_values($minutesByDay);

        $totalMinutes = (int) array_sum($chartUserMinutes);

        $users = User::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('reports.user-activity', [
            'users'            => $users,
            'selectedUserId'   => $selectedUserId,
            'desde'            => $start->toDateString(),
            'hasta'            => $end->toDateString(),
            'sessions'         => $sessions,
            'totalMinutes'     => $totalMinutes,
            'chartUserLabels'  => $chartUserLabels,
            'chartUserMinutes' => $chartUserMinutes,
            'chartDailyLabels' => $chartDailyLabels,
            'chartDailyMinutes'=> $chartDailyMinutes,
        ]);
    }
}
