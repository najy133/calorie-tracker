<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\Entry;
use App\Services\CalorieEstimator;
use Illuminate\Support\Facades\RateLimiter;

class Homepage extends Component
{
    #[Rule('required|string|min:2|max:500')]
    public string $food = '';

    public int $calories = 0;
    public int $todayCalories = 0;
    public int $dailyGoal = 2000;
    public ?string $explanation = null;
    public \Illuminate\Support\Collection $todayEntries;

    public function mount(): void
    {
        $this->refreshTotals();
    }

    public function estimate(): void
    {
        $this->validate();

        $key = 'estimate:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 10)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('food', "Too many requests. Please wait {$seconds} seconds.");
            return;
        }

        RateLimiter::hit($key, decaySeconds: 60);

        try {
            $result = app(CalorieEstimator::class)->estimate($this->food);
            $this->calories = $result['calories'];
            $this->explanation = $result['explanation'];
        } catch (\Throwable $e) {
            $this->addError('food', 'Could not estimate calories. Please try again.');
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

        Entry::create([
            'user_id' => auth()->id(),
            'food' => $this->food,
            'calories' => $this->calories,
        ]);

        $this->refreshTotals();
        $this->reset('food', 'calories', 'explanation');
    }

    public function delete(int $id): void
    {
        Entry::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        $this->refreshTotals();
    }

    private function refreshTotals(): void
    {
        if (!auth()->check()) {
            $this->todayCalories = 0;
            $this->todayEntries = collect();
            return;
        }

        $this->todayCalories = Entry::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->sum('calories');

        $this->todayEntries = Entry::where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'food', 'calories', 'created_at']);
    }

    public function render()
    {
        return view('livewire.homepage')->layout('layouts.app');
    }
}
