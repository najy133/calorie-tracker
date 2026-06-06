<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use RuntimeException;

class AITargetAdvisor
{
    /**
     * Ask the AI for a personalised calorie target and macro split.
     *
     * Falls back to null on failure — caller should use TargetCalculator as fallback.
     *
     * @return array{calories: int, protein: int, carbs: int, fat: int, explanation: string}|null
     */
    public function advise(
        int     $age,
        string  $sex,
        float   $weightKg,
        int     $heightCm,
        string  $activity,
        string  $goal,
        ?string $goalNotes,
        string  $eatingHabit,
        ?string $healthNotes,
        int     $mathTarget,   // fallback from Mifflin formula
    ): ?array {
        try {
            $response = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->withSystemPrompt('You are a registered dietitian and sports nutritionist. Always respond with valid JSON only — no markdown, no code fences, no extra text.')
                ->withPrompt($this->buildPrompt($age, $sex, $weightKg, $heightCm, $activity, $goal, $goalNotes, $eatingHabit, $healthNotes, $mathTarget))
                ->asText()
                ->text;

            $data = json_decode(trim($response), true);

            if (!is_array($data) || !isset($data['calories'], $data['protein'], $data['carbs'], $data['fat'], $data['explanation'])) {
                return null;
            }

            return [
                'calories'    => max(800, (int) $data['calories']),
                'protein'     => max(0,   (int) $data['protein']),
                'carbs'       => max(0,   (int) $data['carbs']),
                'fat'         => max(0,   (int) $data['fat']),
                'explanation' => (string) $data['explanation'],
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildPrompt(
        int     $age,
        string  $sex,
        float   $weightKg,
        int     $heightCm,
        string  $activity,
        string  $goal,
        ?string $goalNotes,
        string  $eatingHabit,
        ?string $healthNotes,
        int     $mathTarget,
    ): string {
        $sexLabel      = $sex === 'M' ? 'male' : 'female';
        $activityLabel = match ($activity) {
            'sedentary' => 'sedentary (desk job, little or no exercise)',
            'light'     => 'lightly active (1–2 workouts per week)',
            'moderate'  => 'moderately active (3–5 workouts per week)',
            'very'      => 'very active (hard training 6–7 days or physical job)',
            default     => $activity,
        };
        $goalLabel = match ($goal) {
            'lose'     => 'lose weight (~0.5 kg per week)',
            'maintain' => 'maintain current weight',
            'build'    => 'build muscle with a small calorie surplus',
            'other'    => $goalNotes ? "custom goal: {$goalNotes}" : 'unspecified custom goal',
            default    => $goal,
        };
        $eatingLabel = match ($eatingHabit) {
            'home' => 'mostly cooks at home (controls ingredients and portions)',
            'out'  => 'mostly eats out (restaurants, takeaway, or delivery)',
            'mix'  => 'a mix of cooking at home and eating out',
            default => $eatingHabit,
        };
        $healthSection = $healthNotes
            ? "Additional context provided by the user: {$healthNotes}"
            : 'No additional health context provided.';

        return <<<PROMPT
        You are setting a personalised daily calorie target for a user. Here is their profile:

        - Age: {$age} years old
        - Sex: {$sexLabel}
        - Weight: {$weightKg} kg
        - Height: {$heightCm} cm
        - Activity level: {$activityLabel}
        - Goal: {$goalLabel}
        - Eating habits: {$eatingLabel}
        - {$healthSection}

        The Mifflin–St Jeor formula suggests {$mathTarget} kcal/day for this profile.

        Your job:
        1. Use that formula result as your starting point.
        2. Adjust it if the user's eating habits, health context, or other factors warrant it. For example: eating out frequently means hidden calories, so you might set a slightly lower target; certain medical conditions may require a different approach.
        3. Set protein, carbs, and fat macros in grams that suit the user's goal and context.
        4. Write a 2–3 sentence explanation that tells the user WHY you set this specific target — reference their actual context (eating habits, health notes), not just the formula.

        Respond ONLY with this JSON (no markdown, no extra keys):
        {
          "calories": integer,
          "protein": integer,
          "carbs": integer,
          "fat": integer,
          "explanation": "string"
        }
        PROMPT;
    }
}
