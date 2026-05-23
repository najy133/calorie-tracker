<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TargetCalculator;

class Onboarding extends Component
{
    public string $step = 'welcome';

    public string  $name      = '';
    public ?int    $age       = null;
    public string  $sex       = '';
    public ?float  $weightKg  = null;
    public ?int    $heightCm  = null;
    public string  $activity  = '';
    public string  $goal      = '';
    public int     $adjust    = 0;

    public int     $bmr       = 0;
    public int     $tdee      = 0;
    public int     $target    = 0;
    public int     $protein   = 0;
    public int     $carbs     = 0;
    public int     $fat       = 0;
    public string  $activityName = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->onboarded_at !== null) {
            $this->redirect(route('home'), navigate: true);
            return;
        }

        $this->name = $user->name ?? '';
    }

    public function chooseAi(): void
    {
        $this->step = 'details';
    }

    public function chooseManual(): void
    {
        auth()->user()->update(['onboarded_at' => now()]);
        $this->redirect(route('profile.edit'), navigate: true);
    }

    public function next(): void
    {
        match ($this->step) {
            'details'  => $this->nextFromDetails(),
            'activity' => $this->nextFromActivity(),
            'goal'     => $this->nextFromGoal(),
            default    => null,
        };
    }

    public function back(): void
    {
        $this->step = match ($this->step) {
            'activity' => 'details',
            'goal'     => 'activity',
            'result'   => 'goal',
            default    => 'welcome',
        };

        if ($this->step === 'goal') {
            $this->adjust = 0;
        }
    }

    public function adjust(int $delta): void
    {
        $this->adjust = max(-500, min(500, $this->adjust + $delta));
    }

    public function proceedAfterCalc(): void
    {
        $calc = app(TargetCalculator::class);

        $result = $calc->calculate(
            $this->age,
            $this->sex,
            $this->weightKg,
            $this->heightCm,
            $this->activity,
            $this->goal,
        );

        $macros = $calc->macroSplit($result['target'], $this->goal);

        $this->bmr     = $result['bmr'];
        $this->tdee    = $result['tdee'];
        $this->target  = $result['target'];
        $this->protein = $macros['protein'];
        $this->carbs   = $macros['carbs'];
        $this->fat     = $macros['fat'];

        $this->activityName = match ($this->activity) {
            'sedentary' => 'sedentary',
            'light'     => 'lightly active',
            'moderate'  => 'moderately active',
            'very'      => 'very active',
            default     => $this->activity,
        };

        $this->step = 'result';
    }

    public function confirm(): void
    {
        $final = $this->target + $this->adjust;

        auth()->user()->update([
            'daily_goal'     => $final,
            'age'            => $this->age,
            'sex'            => $this->sex,
            'weight_kg'      => $this->weightKg,
            'height_cm'      => $this->heightCm,
            'activity_level' => $this->activity,
            'goal'           => $this->goal,
            'onboarded_at'   => now(),
        ]);

        $this->step = 'done';
    }

    private function nextFromDetails(): void
    {
        $this->validate([
            'name'     => 'required|string|min:2|max:100',
            'age'      => 'required|integer|min:13|max:100',
            'sex'      => 'required|in:M,F',
            'weightKg' => 'required|numeric|min:30|max:300',
            'heightCm' => 'required|integer|min:100|max:230',
        ]);

        auth()->user()->update(['name' => $this->name]);
        $this->step = 'activity';
    }

    private function nextFromActivity(): void
    {
        $this->validate([
            'activity' => 'required|in:sedentary,light,moderate,very',
        ]);

        $this->step = 'goal';
    }

    private function nextFromGoal(): void
    {
        $this->validate([
            'goal' => 'required|in:lose,maintain,build',
        ]);

        $this->step = 'calc';
    }

    public function render()
    {
        return view('livewire.onboarding')->layout('layouts.wizard');
    }
}
