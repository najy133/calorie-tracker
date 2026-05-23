<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Concerns\HasStreak;
use App\Models\Entry;
use App\Services\CalorieEstimator;
use Illuminate\Support\Facades\RateLimiter;

class Homepage extends Component
{
    use HasStreak;
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

    public ?int $editingId = null;
    public string $editFood = '';

    public function mount(): void
    {
        $this->dailyGoal     = auth()->check() ? (auth()->user()->daily_goal ?? 2000) : 2000;
        $this->todayCalories = $this->queryTodayCalories();
        $this->streak        = $this->calculateStreak();
    }

    public function estimate(): void
    {
        $this->validate();

        $key = 'estimate:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 10)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('food', __('Too many requests. Please wait :seconds seconds.', ['seconds' => $seconds]));
            return;
        }

        RateLimiter::hit($key, decaySeconds: 60);

        try {
            $result = app(CalorieEstimator::class)->estimate($this->food, app()->getLocale());
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

        Entry::create([
            'user_id'  => auth()->id(),
            'food'     => $this->food,
            'calories' => $this->calories,
            'protein'  => $this->protein,
            'carbs'    => $this->carbs,
            'fat'      => $this->fat,
        ]);

        $this->todayCalories = $this->queryTodayCalories();
        $this->streak        = $this->calculateStreak();
        $this->reset('food', 'calories', 'protein', 'carbs', 'fat', 'breakdown', 'explanation');
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
            $this->addError('editFood', __('Too many requests. Please wait :seconds seconds.', ['seconds' => $seconds]));
            return;
        }

        RateLimiter::hit($key, decaySeconds: 60);

        try {
            $result = app(CalorieEstimator::class)->estimate($this->editFood, app()->getLocale());

            Entry::where('id', $this->editingId)
                ->where('user_id', auth()->id())
                ->update([
                    'food'     => $this->editFood,
                    'calories' => $result['calories'],
                    'protein'  => $result['protein'],
                    'carbs'    => $result['carbs'],
                    'fat'      => $result['fat'],
                ]);

            $this->todayCalories = $this->queryTodayCalories();
            $this->cancelEdit();
        } catch (\Throwable $e) {
            $this->addError('editFood', __('Could not estimate calories. Please try again.'));
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
