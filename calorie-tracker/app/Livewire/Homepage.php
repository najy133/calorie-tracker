<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Concerns\HasStreak;
use App\Concerns\HasEntryActions;
use App\Models\Entry;
use App\Services\CalorieEstimator;
use Illuminate\Support\Facades\RateLimiter;

class Homepage extends Component
{
    use HasStreak, HasEntryActions;

    #[Rule('required|string|min:2|max:500')]
    public string $food = '';

    public int $calories = 0;
    public int $protein = 0;
    public int $carbs = 0;
    public int $fat = 0;
    public array $breakdown = [];
    public int $todayCalories = 0;
    public int $dailyGoal = 2000;
    public int $streak = 0;
    public ?string $explanation = null;

    // The entry created by the last save — its row flashes briefly as feedback
    public ?int $lastSavedId = null;

    public function mount(): void
    {
        $this->dailyGoal     = auth()->check() ? (auth()->user()->daily_goal ?? 2000) : 2000;
        $this->todayCalories = $this->queryTodayCalories();
        $this->streak        = $this->calculateStreak();
    }

    public function updatedFood(): void
    {
        // Editing the food text invalidates the current estimate — clear it so the
        // stale preview (and its Save button) can't apply to the new text.
        $this->reset('calories', 'protein', 'carbs', 'fat', 'breakdown', 'explanation');
        $this->resetErrorBag('food');
    }

    public function estimate(): void
    {
        $this->validate();

        $key = $this->estimateRateKey();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 10)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('food', __('Too many requests. Please wait :seconds seconds.', ['seconds' => $seconds]));
            return;
        }

        RateLimiter::hit($key, decaySeconds: 60);

        try {
            $result = app(CalorieEstimator::class)->estimate($this->food, app()->getLocale());

            if ($result['not_food']) {
                // Clear any previous estimate so a stale value can't linger (or be saved)
                $this->reset('calories', 'protein', 'carbs', 'fat', 'breakdown', 'explanation');
                $this->addError('food', __("That doesn't look like food. Try something like \"chicken sandwich\" or \"2 eggs and toast\"."));
                return;
            }

            $this->calories    = $result['calories'];
            $this->protein     = $result['protein'];
            $this->carbs       = $result['carbs'];
            $this->fat         = $result['fat'];
            $this->breakdown   = $result['breakdown'];
            $this->explanation = $result['explanation'];
        } catch (\Throwable $e) {
            $this->addError('food', __('Could not estimate calories. Please try again.'));
            \Log::error('CalorieEstimator failed', ['error' => $e->getMessage(), 'food' => $this->food]);
        }
    }

    public function save(): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        if ($this->calories <= 0 || blank($this->food)) return;

        $entry = Entry::create([
            'user_id'  => auth()->id(),
            'food'     => $this->food,
            'calories' => $this->calories,
            'protein'  => $this->protein,
            'carbs'    => $this->carbs,
            'fat'      => $this->fat,
        ]);

        $this->lastSavedId = $entry->id;

        $this->todayCalories = $this->queryTodayCalories();
        $this->streak        = $this->calculateStreak();
        $this->reset('food', 'calories', 'protein', 'carbs', 'fat', 'breakdown', 'explanation');
    }

    // Edit + confirm-delete live in the shared HasEntryActions trait; this hook
    // keeps the cached today-total and streak in sync after an edit or delete.
    protected function refreshAfterEntryChange(): void
    {
        $this->todayCalories = $this->queryTodayCalories();
        $this->streak        = $this->calculateStreak();
    }

    private function queryTodayCalories(): int
    {
        if (!auth()->check()) return 0;

        return Entry::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->sum('calories');
    }

    public function render()
    {
        $todayEntries = auth()->check()
            ? Entry::where('user_id', auth()->id())
                ->whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->get(['id', 'food', 'calories', 'protein', 'carbs', 'fat', 'created_at'])
            : collect();

        return view('livewire.homepage', compact('todayEntries'))->layout('layouts.app');
    }
}
