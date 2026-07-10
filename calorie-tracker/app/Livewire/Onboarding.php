<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\TargetCalculator;
use App\Services\AITargetAdvisor;

class Onboarding extends Component
{
    /** The ordered wizard steps — the single source of truth for navigation. */
    public const STEPS = ['welcome', 'details', 'activity', 'goal', 'eating', 'context', 'result'];

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
    public int     $previousTarget = 0; // the user's existing daily_goal, for the old → new delta on Result
    public int     $protein      = 0;
    public int     $carbs        = 0;
    public int     $fat          = 0;
    public string  $activityName = '';
    public string  $aiExplanation = '';

    // Fingerprint of the inputs used for the last calculation — lets us skip
    // recalculating when nothing changed
    public string  $calcFingerprint = '';

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->onboarded_at !== null && !request()->boolean('recalculate')) {
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
        $this->previousTarget = (int) ($user->daily_goal ?? 0);

        // Returning user (recalculate flow): restore the saved result so jumping
        // to Result shows it instantly instead of recalculating
        if ($user->target_protein !== null && $user->daily_goal && $this->age && $this->sex && $this->weightKg && $this->heightCm && $this->activity && $this->goal) {
            $this->target        = $user->daily_goal;
            $this->protein       = $user->target_protein;
            $this->carbs         = $user->target_carbs ?? 0;
            $this->fat           = $user->target_fat ?? 0;
            $this->aiExplanation = $user->ai_explanation ?? '';

            $result = app(TargetCalculator::class)->calculate(
                $this->age, $this->sex, $this->weightKg, $this->heightCm, $this->activity, $this->goal,
            );
            $this->bmr  = $result['bmr'];
            $this->tdee = $result['tdee'];

            $this->activityName = match ($this->activity) {
                'sedentary' => __('sedentary'),
                'light'     => __('lightly active'),
                'moderate'  => __('moderately active'),
                'very'      => __('very active'),
                default     => $this->activity,
            };

            $this->calcFingerprint = $this->inputFingerprint();
        }
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
        // Step back one along STEPS ('calc' isn't in STEPS, so it falls back to context).
        $idx = array_search($this->step, self::STEPS, true);
        $prev = ($idx === false || $idx === 0) ? 'welcome' : self::STEPS[$idx - 1];
        $this->step = ($this->step === 'calc') ? 'context' : $prev;

        if ($this->step !== 'result') {
            $this->adjust = 0;
        }
    }

    public function jumpTo(string $target): void
    {
        if (!in_array($target, self::STEPS, true)) return;

        if ($target === 'result' && $this->needsCalculation()) {
            $complete = $this->age && $this->sex && $this->weightKg && $this->heightCm && $this->activity && $this->goal;
            $this->step = $complete ? 'calc' : 'details';
            return;
        }

        $this->step = $target;
        if ($target !== 'result') {
            $this->adjust = 0;
        }
    }

    public function nudgeTarget(int $delta): void
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
            locale:       app()->getLocale(),
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
            'sedentary' => __('sedentary'),
            'light'     => __('lightly active'),
            'moderate'  => __('moderately active'),
            'very'      => __('very active'),
            default     => $this->activity,
        };

        $this->calcFingerprint = $this->inputFingerprint();
        $this->step = 'result';
    }

    private function inputFingerprint(): string
    {
        return md5(json_encode([
            $this->age, $this->sex, $this->weightKg, $this->heightCm,
            $this->activity, $this->goal, $this->goalNotes,
            $this->eatingHabit, $this->healthNotes,
        ]));
    }

    private function needsCalculation(): bool
    {
        return $this->target === 0 || $this->calcFingerprint !== $this->inputFingerprint();
    }

    public function goHome(): void
    {
        $this->redirect(route('home'), navigate: true);
    }

    public function confirm(): void
    {
        // Public Livewire properties are client-writable, so the synced target,
        // adjust, and profile fields can't be trusted — re-validate the inputs
        // and clamp the derived numbers before persisting (the UI's ±500 clamp
        // and step validations are all bypassable via the wire protocol).
        $this->validate([
            'age'         => 'required|integer|min:13|max:100',
            'sex'         => 'required|in:M,F',
            'weightKg'    => 'required|numeric|min:30|max:300',
            'heightCm'    => 'required|integer|min:100|max:230',
            'activity'    => 'required|in:sedentary,light,moderate,very',
            'goal'        => 'required|in:lose,maintain,build,other',
            'eatingHabit' => 'required|in:home,out,mix',
            'goalNotes'   => 'nullable|string|max:500',
            'healthNotes' => 'nullable|string|max:1000',
        ]);

        $final = max(800, min(10000, $this->target + $this->adjust));

        auth()->user()->update([
            'daily_goal'     => $final,
            'target_protein' => max(0, min(1000, $this->protein)),
            'target_carbs'   => max(0, min(2000, $this->carbs)),
            'target_fat'     => max(0, min(1000, $this->fat)),
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
        $this->step = $this->needsCalculation() ? 'calc' : 'result';
    }

    public function render()
    {
        // Derive the stepper from STEPS so the view can't drift from the step machine.
        $navSteps = array_map(
            fn (string $id) => ['id' => $id, 'label' => __(ucfirst($id))],
            self::STEPS,
        );

        return view('livewire.onboarding', compact('navSteps'))->layout('layouts.wizard');
    }
}
