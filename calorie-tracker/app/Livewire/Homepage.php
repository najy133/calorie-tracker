<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Rule;
use App\Models\Entry;
use App\Services\CalorieEstimator;

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
        if ($this->calories <= 0 || blank($this->food)) return;

        Entry::create([
            'food' => $this->food,
            'calories' => $this->calories,
        ]);

        $this->refreshTotals();
        $this->reset('food', 'calories', 'explanation');
    }

    public function delete(int $id): void
    {
        Entry::where('id', $id)->delete();
        $this->refreshTotals();
    }

    private function refreshTotals(): void
    {
        $this->todayCalories = Entry::whereDate('created_at', today())->sum('calories');
        $this->todayEntries = Entry::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'food', 'calories', 'created_at']);
    }

    public function render()
    {
        return view('livewire.homepage')->layout('layouts.app');
    }
}
