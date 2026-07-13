<?php

namespace App\Services;

use App\Support\AiBudget;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use RuntimeException;

class CalorieEstimator
{
    /**
     * @return array{not_food: bool, calories?: int, protein?: int, carbs?: int, fat?: int, explanation?: string|null, breakdown?: array}
     * @throws RuntimeException
     */
    public function estimate(string $food, string $locale = 'en'): array
    {
        AiBudget::guard();

        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o-mini')
            ->withSystemPrompt('You are a nutrition expert. Always respond with valid JSON only — no markdown, no code fences, no extra text.')
            ->withPrompt($this->buildPrompt($food, $locale))
            // temperature 0 → the same food description returns the same numbers,
            // instead of re-rolling a different estimate on every call.
            ->usingTemperature(0)
            // Bound the request so a slow/hung OpenAI call can't pin a PHP worker.
            ->withClientOptions(['timeout' => 20, 'connect_timeout' => 8])
            ->asText()
            ->text;

        $data = json_decode(trim($response), true);

        if (is_array($data) && ($data['error'] ?? null) === 'not_food') {
            return ['not_food' => true];
        }

        if (!is_array($data) || !array_key_exists('calories', $data)) {
            throw new RuntimeException("Unexpected AI response: {$response}");
        }

        return [
            'not_food'    => false,
            'calories'    => max(0, (int) ($data['calories'] ?? 0)),
            'protein'     => max(0, (int) ($data['protein'] ?? 0)),
            'carbs'       => max(0, (int) ($data['carbs'] ?? 0)),
            'fat'         => max(0, (int) ($data['fat'] ?? 0)),
            'explanation' => isset($data['explanation']) ? (string) $data['explanation'] : null,
            'breakdown'   => $this->parseBreakdown($data['breakdown'] ?? []),
        ];
    }

    private function parseBreakdown(mixed $raw): array
    {
        if (!is_array($raw)) return [];

        return array_values(array_filter(array_map(function ($item) {
            if (!is_array($item) || !isset($item['text'])) return null;
            return [
                'text' => (string) ($item['text'] ?? ''),
                'kcal' => max(0, (int) ($item['kcal'] ?? 0)),
                'p'    => max(0, (int) ($item['p'] ?? 0)),
                'c'    => max(0, (int) ($item['c'] ?? 0)),
                'f'    => max(0, (int) ($item['f'] ?? 0)),
            ];
        }, $raw)));
    }

    private function buildPrompt(string $food, string $locale = 'en'): string
    {
        $langNote = $locale === 'ar'
            ? "\n        - Write the explanation field in Arabic."
            : '';

        return <<<PROMPT
        Estimate the nutrition for the following food input and return a JSON object.

        Required schema:
        {
          "calories": integer,
          "protein": integer,
          "carbs": integer,
          "fat": integer,
          "explanation": string,
          "breakdown": [{"text": string, "kcal": integer, "p": integer, "c": integer, "f": integer}]
        }

        All numeric fields are integers. Protein, carbs, and fat are in grams.
        The "breakdown" array has one entry per distinct food item or ingredient.
        The sum of breakdown[].kcal should equal "calories".

        Rules:
        - Any edible item, meal, quantity, or restaurant reference is valid food.
        - Interpret misspellings, abbreviations, and partial spellings as the food the user most likely meant (e.g. "appl" -> apple, "chikn" -> chicken, "banan" -> banana, "spaghtti" -> spaghetti). Lean toward recognising a food.
        - Treat branded meals and fast food as valid (even if misspelled or regional).
        - If quantity or preparation details are missing, assume a standard single serving.
        - If a known food item is mentioned, estimate it regardless of brand name.
        - Only return exactly {"error": "not_food"} when the input is clearly NOT an attempt to name a food — random characters (e.g. "asdfgh"), greetings, questions, or unrelated objects. When in doubt, treat it as food.
        - The explanation must: (1) state the assumption made about serving size or preparation, (2) mention in one short sentence what extra detail would improve accuracy.{$langNote}

        Examples:
        Input: 4 chicken breasts
        Output: {"calories": 800, "protein": 120, "carbs": 0, "fat": 20, "explanation": "Assumed medium grilled chicken breasts (~200g each). Specify size or cooking method for a better estimate.", "breakdown": [{"text": "4 chicken breasts", "kcal": 800, "p": 120, "c": 0, "f": 20}]}

        Input: 2 eggs, toast with butter
        Output: {"calories": 320, "protein": 15, "carbs": 15, "fat": 22, "explanation": "Assumed 2 large eggs scrambled, one slice of toast, and 1 tsp butter.", "breakdown": [{"text": "2 eggs", "kcal": 140, "p": 12, "c": 1, "f": 10}, {"text": "toast", "kcal": 80, "p": 3, "c": 14, "f": 1}, {"text": "butter", "kcal": 100, "p": 0, "c": 0, "f": 11}]}

        Input: appl
        Output: {"calories": 95, "protein": 0, "carbs": 25, "fat": 0, "explanation": "Interpreted as one medium apple (~180g). Specify size or variety for a better estimate.", "breakdown": [{"text": "apple", "kcal": 95, "p": 0, "c": 25, "f": 0}]}

        Input: my homework
        Output: {"error": "not_food"}

        Input: {$food}
        Output:
        PROMPT;
    }
}
