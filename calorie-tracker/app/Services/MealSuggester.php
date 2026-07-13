<?php

namespace App\Services;

use App\Support\AiBudget;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;

class MealSuggester
{
    /**
     * Suggest a few meal ideas that fit the user's remaining calories and context.
     *
     * Returns [] on failure — the caller shows a graceful error.
     *
     * @return array<int, array{name: string, calories: int, protein: int, carbs: int, fat: int, why: string}>
     */
    public function suggest(
        int     $remainingCalories,
        string  $mealType,
        string  $goal,
        string  $eatingHabit,
        ?string $healthNotes,
        ?string $goalNotes,
        string  $locale = 'en',
    ): array {
        try {
            AiBudget::guard();

            $response = Prism::text()
                ->using(Provider::OpenAI, 'gpt-4o-mini')
                ->withSystemPrompt('You are a friendly nutritionist suggesting meal ideas. You are not a doctor. Always respond with valid JSON only — no markdown, no code fences, no extra text.')
                ->withPrompt($this->buildPrompt($remainingCalories, $mealType, $goal, $eatingHabit, $healthNotes, $goalNotes, $locale))
                // Variety is wanted here (unlike the estimator), so run warm.
                ->usingTemperature(0.8)
                ->withClientOptions(['timeout' => 25, 'connect_timeout' => 8])
                ->asText()
                ->text;

            $data = json_decode(trim($response), true);

            if (!is_array($data) || !isset($data['suggestions']) || !is_array($data['suggestions'])) {
                return [];
            }

            return array_values(array_filter(array_map(function ($s) {
                if (!is_array($s) || !isset($s['name'])) return null;
                return [
                    'name'     => (string) $s['name'],
                    'calories' => max(0, (int) ($s['calories'] ?? 0)),
                    'protein'  => max(0, (int) ($s['protein'] ?? 0)),
                    'carbs'    => max(0, (int) ($s['carbs'] ?? 0)),
                    'fat'      => max(0, (int) ($s['fat'] ?? 0)),
                    'why'      => (string) ($s['why'] ?? ''),
                ];
            }, $data['suggestions'])));
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildPrompt(
        int     $remainingCalories,
        string  $mealType,
        string  $goal,
        string  $eatingHabit,
        ?string $healthNotes,
        ?string $goalNotes,
        string  $locale,
    ): string {
        $goalLabel = match ($goal) {
            'lose'     => 'losing weight',
            'maintain' => 'maintaining weight',
            'build'    => 'building muscle',
            'other'    => $goalNotes ? "this custom goal: {$goalNotes}" : 'their goal',
            default    => 'their goal',
        };
        $eatingLabel = match ($eatingHabit) {
            'home' => 'mostly cooks at home',
            'out'  => 'mostly eats out (restaurants/takeaway)',
            'mix'  => 'eats a mix of home-cooked and out',
            default => 'has no strong eating-habit preference',
        };
        $budgetNote = $remainingCalories > 0
            ? "They have about {$remainingCalories} kcal left in their daily budget — each idea should fit comfortably within that."
            : "They are at or over their daily budget — suggest light, low-calorie options.";
        $context = trim((string) $healthNotes) !== ''
            ? "IMPORTANT — honour this context the user gave about themselves (dietary needs, restrictions, dislikes, or health): \"{$healthNotes}\". Never suggest anything that conflicts with it."
            : 'No specific dietary restrictions were provided.';
        $langNote = $locale === 'ar' ? "\n        - Write the \"name\" and \"why\" fields in Arabic." : '';

        return <<<PROMPT
        Suggest exactly 3 {$mealType} ideas for a user and return a JSON object.

        About the user:
        - Goal: {$goalLabel}
        - {$eatingLabel}
        - {$budgetNote}
        - {$context}

        Rules:
        - Return realistic, appetising, everyday {$mealType} options — not restrictive "diet food" unless the context calls for it.
        - Give a rough calorie estimate and macros (grams) per idea; they are estimates, not exact.
        - Keep each "why" to one short sentence on why it suits this user.{$langNote}

        Respond ONLY with this JSON:
        {
          "suggestions": [
            {"name": string, "calories": integer, "protein": integer, "carbs": integer, "fat": integer, "why": string}
          ]
        }
        PROMPT;
    }
}
