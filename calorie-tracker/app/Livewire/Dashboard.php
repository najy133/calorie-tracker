<?php

namespace App\Livewire;

use Livewire\Component;
use App\Concerns\HasStreak;
use App\Models\Entry;
use App\Services\CalorieEstimator;
use Illuminate\Support\Facades\RateLimiter;

class Dashboard extends Component
{
    use HasStreak;
    public int $dailyGoal;
    public int $streak;

    public ?int $editingId = null;
    public string $editFood = '';

    // How many days of history to show before "Show earlier"
    public int $visibleDays = 3;

    public function mount(): void
    {
        $this->dailyGoal = auth()->user()->daily_goal ?? 2000;
        $this->streak    = $this->calculateStreak();
    }

    public function showMore(): void
    {
        $this->visibleDays += 4;
    }

    public function startEdit(int $id): void
    {
        $entry = Entry::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $this->editingId = $id;
        $this->editFood  = $entry->food;
    }

    public function saveEdit(): void
    {
        if (!$this->editingId || blank($this->editFood)) return;

        $key = 'estimate:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 10)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('editFood', "Too many requests. Please wait {$seconds} seconds.");
            return;
        }

        RateLimiter::hit($key, decaySeconds: 60);

        try {
            $result = app(CalorieEstimator::class)->estimate($this->editFood);

            if ($result['not_food']) {
                $this->addError('editFood', __("That doesn't look like food. Try something like \"chicken sandwich\" or \"2 eggs and toast\"."));
                return;
            }

            Entry::where('id', $this->editingId)
                ->where('user_id', auth()->id())
                ->update([
                    'food'     => $this->editFood,
                    'calories' => $result['calories'],
                    'protein'  => $result['protein'],
                    'carbs'    => $result['carbs'],
                    'fat'      => $result['fat'],
                ]);

            $this->cancelEdit();
        } catch (\Throwable $e) {
            $this->addError('editFood', 'Could not estimate calories. Please try again.');
            \Log::error('CalorieEstimator failed on edit', ['error' => $e->getMessage(), 'food' => $this->editFood]);
        }
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editFood  = '';
    }

    public function delete(int $id): void
    {
        Entry::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();
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

        // History grouped by day
        $entriesByDay = Entry::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get(['id', 'food', 'calories', 'protein', 'carbs', 'fat', 'created_at'])
            ->groupBy(fn ($e) => $e->created_at->toDateString());

        $totalDays     = $entriesByDay->count();
        $recentEntries = $entriesByDay->take($this->visibleDays);
        $hasMoreDays   = $totalDays > $this->visibleDays;

        return view('livewire.dashboard', compact('weeklyData', 'weeklyAverage', 'recentEntries', 'hasMoreDays'))
            ->layout('layouts.app');
    }
}
