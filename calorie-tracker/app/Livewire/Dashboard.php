<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Entry;

class Dashboard extends Component
{
    public int $dailyGoal;
    public int $streak;

    public ?int $editingId = null;
    public string $editFood = '';
    public int $editCalories = 0;
    public int $editProtein = 0;
    public int $editCarbs = 0;
    public int $editFat = 0;

    public function mount(): void
    {
        $this->dailyGoal = auth()->user()->daily_goal ?? 2000;
        $this->streak    = $this->calculateStreak();
    }

    public function startEdit(int $id): void
    {
        $entry = Entry::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $this->editingId    = $id;
        $this->editFood     = $entry->food;
        $this->editCalories = $entry->calories;
        $this->editProtein  = $entry->protein;
        $this->editCarbs    = $entry->carbs;
        $this->editFat      = $entry->fat;
    }

    public function saveEdit(): void
    {
        if (!$this->editingId || blank($this->editFood) || $this->editCalories <= 0) return;

        Entry::where('id', $this->editingId)
            ->where('user_id', auth()->id())
            ->update([
                'food'     => $this->editFood,
                'calories' => $this->editCalories,
                'protein'  => $this->editProtein,
                'carbs'    => $this->editCarbs,
                'fat'      => $this->editFat,
            ]);

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editingId    = null;
        $this->editFood     = '';
        $this->editCalories = 0;
        $this->editProtein  = 0;
        $this->editCarbs    = 0;
        $this->editFat      = 0;
    }

    public function delete(int $id): void
    {
        Entry::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();
    }

    private function calculateStreak(): int
    {
        $streak = 0;
        $day    = today();

        while (Entry::where('user_id', auth()->id())->whereDate('created_at', $day)->exists()) {
            $streak++;
            $day = $day->subDay();
        }

        return $streak;
    }

    public function render()
    {
        $userId = auth()->id();

        // Weekly bar chart data — last 7 days
        $totals = Entry::where('user_id', $userId)
            ->whereBetween('created_at', [today()->subDays(6)->startOfDay(), now()])
            ->selectRaw('DATE(created_at) as date, SUM(calories) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $weeklyData = collect(range(6, 0))->map(function ($daysAgo) use ($totals) {
            $date     = today()->subDays($daysAgo);
            $calories = (int) ($totals[$date->toDateString()] ?? 0);

            return [
                'label'    => $date->format('D'),
                'date'     => $date->toDateString(),
                'calories' => $calories,
                'pct'      => $this->dailyGoal > 0 ? min(100, round(($calories / $this->dailyGoal) * 100)) : 0,
                'isToday'  => $date->isToday(),
            ];
        });

        $weeklyAverage = (int) round($weeklyData->average('calories'));

        // History grouped by day — last 50 entries
        $recentEntries = Entry::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['id', 'food', 'calories', 'protein', 'carbs', 'fat', 'created_at'])
            ->groupBy(fn ($e) => $e->created_at->toDateString());

        return view('livewire.dashboard', compact('weeklyData', 'weeklyAverage', 'recentEntries'))
            ->layout('layouts.app');
    }
}
