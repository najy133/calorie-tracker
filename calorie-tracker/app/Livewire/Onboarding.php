<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TargetCalculator;
use App\Services\AITargetAdvisor;

class Onboarding extends Component
{
    public string $step = 'welcome';

    public string  $name         = '';
    public ?int    $age          = null;
    public string  $sex          = '';
    public ?float  $weightKg     = null;
    public ?int    $heightCm     = null;
    public string  $activity     = '';
    public string  $goal         = '';
    public string  $goalNotes    = '';
    public string  $eatingHabit  = '';
    public string  $healthNotes  = '';
    public int     $adjust       = 0;

    public int     $bmr          = 0;
    public int     $tdee         = 0;
    public int     $target       = 0;
    public int     $protein      = 0;
    public int     $carbs        = 0;
    public int     $fat          = 0;
    public string  $activityName = '';
    public string  $aiExplanation = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->onboarded_at !== null) {
            $this->redirect(route('home'), navigate: true);
            return;
        }

        $this->name        = $user->name          ?? '';
        $this->age         = $user->age            ?? null;
        $this->sex         = $user->sex            ?? '';
        $this->weightKg    = $user->weight_kg      ?? null;
        $this->heightCm    = $user->height_cm      ?? null;
        $this->activity    = $user->activity_level ?? '';
        $this->goal        = $user->goal           ?? '';
        $this->goalNotes   = $user->goal_notes     ?? '';
        $this->eatingHabit = $user->eating_habit   ?? '';
        $this->healthNotes = $user->health_notes   ?? '';
    }

    public function chooseAi(): void
    {
        $this->step = 'details';
    }

    public function selectActivity(string $value): void
    {
        $this->activity = $value;
    }

    public function selectGoal(string $value): void
    {
        $this->goal = $value;
    }

    public function selectSex(string $value): void
    {
        $this->sex = $value;
    }

    public function selectEatingHabit(string $value): void
    {
        $this->eatingHabit = $value;
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
            'eating'   => $this->nextFromEating(),
            'context'  => $this->nextFromContext(),
            default    => null,
        };
    }

    public function back(): void
    {
        $this->step = match ($this->step) {
            'activity' => 'details',
            'goal'     => 'activity',
            'eating'   => 'goal',
            'context'  => 'eating',
            'result'   => 'context',
            default    => 'welcome',
        };

        if (!in_array($this->step, ['result'])) {
            $this->adjust = 0;
        }
    }

    public function jumpTo(string $target): void
    {
        $allowed = ['welcome', 'details', 'activity', 'goal', 'eating', 'context', 'result'];

        if (!in_array($target, $allowed)) return;

        $this->step = $target;
        if ($target !== 'result') {
            $this->adjust = 0;
        }
    }

    public function adjust(int $delta): void
    {
        $this->adjust = max(-500, min(500, $this->adjust + $delta));
    }

    public function proceedAfterCalc(): void
    {
        // Math baseline (always runs, used as fallback)
        $calc   = app(TargetCalculator::class);
        // For math fallback, treat 'other' as 'maintain' — AI will override with proper value
        $mathGoal = $this->goal === 'other' ? 'maintain' : $this->goal;

        $result = $calc->calculate(
            $this->age,
            $this->sex,
            $this->weightKg,
            $this->heightCm,
            $this->activity,
            $mathGoal,
        );

        $this->bmr  = $result['bmr'];
        $this->tdee = $result['tdee'];

        // Try AI — it gets the full context and can deviate from the formula
        $ai = app(AITargetAdvisor::class)->advise(
            age:          $this->age,
            sex:          $this->sex,
            weightKg:     $this->weightKg,
            heightCm:     $this->heightCm,
            activity:     $this->activity,
            goal:         $this->goal,
            goalNotes:    $this->goalNotes ?: null,
            eatingHabit:  $this->eatingHabit,
            healthNotes:  $this->healthNotes ?: null,
            mathTarget:   $result['target'],
        );

        if ($ai) {
            $this->target        = $ai['calories'];
            $this->protein       = $ai['protein'];
            $this->carbs         = $ai['carbs'];
            $this->fat           = $ai['fat'];
            $this->aiExplanation = $ai['explanation'];
        } else {
            // Fallback to pure math
            $macros = $calc->macroSplit($result['target'], $mathGoal);
            $this->target        = $result['target'];
            $this->protein       = $macros['protein'];
            $this->carbs         = $macros['carbs'];
            $this->fat           = $macros['fat'];
            $this->aiExplanation = '';
        }

        $this->activityName = match ($this->activity) {
            'sedentary' => 'sedentary',
            'light'     => 'lightly active',
            'moderate'  => 'moderately active',
            'very'      => 'very active',
            default     => $this->activity,
        };

        $this->step = 'result';
    }

    public function goHome(): void
    {
        $this->redirect(route('home'), navigate: true);
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
            'goal_notes'     => $this->goalNotes ?: null,
            'eating_habit'   => $this->eatingHabit,
            'health_notes'   => $this->healthNotes ?: null,
            'ai_explanation' => $this->aiExplanation ?: null,
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
            'goal'      => 'required|in:lose,maintain,build,other',
            'goalNotes' => 'required_if:goal,other|nullable|string|max:500',
        ]);

        $this->step = 'eating';
    }

    private function nextFromEating(): void
    {
        $this->validate([
            'eatingHabit' => 'required|in:home,out,mix',
        ]);

        $this->step = 'context';
    }

    private function nextFromContext(): void
    {
        // health notes are optional — no validation required
        $this->step = 'calc';
    }

    public function render()
    {
        return view('livewire.onboarding')->layout('layouts.wizard');
    }
}
