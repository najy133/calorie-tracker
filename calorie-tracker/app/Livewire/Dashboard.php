<?php

namespace App\Livewire;

use Livewire\Component;
use App\Concerns\HasStreak;
use App\Concerns\HasEntryActions;
use App\Models\Entry;

class Dashboard extends Component
{
    use HasStreak, HasEntryActions;

    public int $dailyGoal;
    public int $streak;

    // How many days of history to show before "Show earlier"
    public int $visibleDays = 3;

    // History filter: 'week' (the shown week) | 'all' | a specific date 'Y-m-d'
    public string $historyFilter = 'week';

    // Which week the chart + history show (weeks back from the current one; 0 = this week)
    public int $weekOffset = 0;

    // Bound to the "jump to date" picker
    public ?string $jumpDate = null;

    public function prevWeek(): void
    {
        $this->weekOffset++;
        $this->historyFilter = 'week';
        $this->visibleDays   = 3;
    }

    public function nextWeek(): void
    {
        $this->weekOffset    = max(0, $this->weekOffset - 1);
        $this->historyFilter = 'week';
        $this->visibleDays   = 3;
    }

    public function updatedJumpDate(?string $value): void
    {
        if (blank($value)) return;

        try {
            $date = \Carbon\Carbon::parse($value);
        } catch (\Throwable) {
            return;
        }

        if ($date->isFuture()) $date = today();

        // Move the chart to the 7-day window ending near that date, and filter history to it
        $daysAgo             = (int) $date->copy()->startOfDay()->diffInDays(today());
        $this->weekOffset    = intdiv(max(0, $daysAgo), 7);
        $this->historyFilter = $date->toDateString();
        $this->visibleDays   = 3;
    }

    public function mount(): void
    {
        $this->dailyGoal = auth()->user()->daily_goal ?? 2000;
        $this->streak    = $this->calculateStreak();
    }

    public function showMore(): void
    {
        $this->visibleDays += 4;
    }

    public function setFilter(string $filter): void
    {
        if ($filter === 'week') {
            $this->weekOffset    = 0;
            $this->historyFilter = 'week';
            $this->jumpDate      = null;
        } elseif ($filter === 'all') {
            $this->historyFilter = 'all';
        }
        $this->visibleDays = 3;
    }

    // Clicking a chart bar filters history to that day; clicking it again clears back to the window
    public function selectDay(string $date): void
    {
        $this->historyFilter = ($this->historyFilter === $date) ? 'week' : $date;
        $this->visibleDays   = 3;
    }

    public function render()
    {
        $userId = auth()->id();

        // ── The shown window: a rolling 7 days ending at (today − weekOffset weeks) ──
        // Rolling (not calendar Mon–Sun) so the chart is always full, never sparse early in a week.
        $windowEnd   = today()->subDays($this->weekOffset * 7);
        $windowStart = $windowEnd->copy()->subDays(6);

        $weekAgg = Entry::where('user_id', $userId)
            ->whereBetween('created_at', [$windowStart->copy()->startOfDay(), $windowEnd->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(calories) c, SUM(protein) p, SUM(carbs) cb, SUM(fat) f')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $weeklyData = collect(range(0, 6))->map(function ($i) use ($windowStart, $weekAgg) {
            $date     = $windowStart->copy()->addDays($i);
            $row      = $weekAgg[$date->toDateString()] ?? null;
            $calories = (int) ($row->c ?? 0);

            return [
                'label'    => $date->locale(app()->getLocale())->translatedFormat('D'),
                'date'     => $date->toDateString(),
                'calories' => $calories,
                'protein'  => (int) ($row->p ?? 0),
                'carbs'    => (int) ($row->cb ?? 0),
                'fat'      => (int) ($row->f ?? 0),
                'pct'      => $this->dailyGoal > 0 ? (int) round(($calories / $this->dailyGoal) * 100) : 0,
                'isToday'  => $date->isToday(),
            ];
        });

        $weeklyAverage = (int) round($weeklyData->avg('calories'));

        $chartMax = (int) ceil(max($this->dailyGoal, $weeklyData->max('calories'), 1) * 1.08);

        $weekLabel = $this->weekOffset === 0
            ? __('Last 7 days')
            : $windowStart->locale(app()->getLocale())->translatedFormat('M j') . ' – ' . $windowEnd->locale(app()->getLocale())->translatedFormat('M j');
        $canGoNext    = $this->weekOffset > 0;
        $isDayView    = ! in_array($this->historyFilter, ['week', 'all'], true);
        $selectedDate = $isDayView ? $this->historyFilter : null;

        // Today's totals — powers the hero
        $today = Entry::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->selectRaw('COALESCE(SUM(calories),0) c, COALESCE(SUM(protein),0) p, COALESCE(SUM(carbs),0) cb, COALESCE(SUM(fat),0) f')
            ->first();

        $todayCalories = (int) $today->c;
        $todayProtein  = (int) $today->p;
        $todayCarbs    = (int) $today->cb;
        $todayFat      = (int) $today->f;
        $todayRemaining = max(0, $this->dailyGoal - $todayCalories);
        $todayPct       = $this->dailyGoal > 0 ? (int) round($todayCalories / $this->dailyGoal * 100) : 0;

        // History grouped by day — matches the shown week, a specific day, or everything
        $historyQuery = Entry::where('user_id', $userId)->orderBy('created_at', 'desc');

        if ($this->historyFilter === 'week') {
            $historyQuery->whereBetween('created_at', [$windowStart->copy()->startOfDay(), $windowEnd->copy()->endOfDay()]);
        } elseif ($this->historyFilter !== 'all') {
            $historyQuery->whereDate('created_at', $this->historyFilter);
        }

        $entriesByDay = $historyQuery
            ->limit(200)
            ->get(['id', 'food', 'calories', 'protein', 'carbs', 'fat', 'source', 'created_at'])
            ->groupBy(fn ($e) => $e->created_at->toDateString());

        // Progressive "show earlier" only applies to the unfiltered "all" view
        if ($this->historyFilter === 'all') {
            $recentEntries = $entriesByDay->take($this->visibleDays);
            $hasMoreDays   = $entriesByDay->count() > $this->visibleDays;
        } else {
            $recentEntries = $entriesByDay;
            $hasMoreDays   = false;
        }

        return view('livewire.dashboard', compact(
            'weeklyData', 'weeklyAverage', 'chartMax', 'recentEntries', 'hasMoreDays',
            'todayCalories', 'todayProtein', 'todayCarbs', 'todayFat', 'todayRemaining', 'todayPct',
            'weekLabel', 'canGoNext', 'isDayView', 'selectedDate',
        ))->layout('layouts.app');
    }
}
