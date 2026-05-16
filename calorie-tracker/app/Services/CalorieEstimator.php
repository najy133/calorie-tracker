<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use RuntimeException;

class CalorieEstimator
{
    /**
     * @return array{calories: int, protein: int, carbs: int, fat: int, explanation: string|null}
     * @throws RuntimeException
     */
    public function estimate(string $food): array
    {
        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o-mini')
            ->withSystemPrompt('You are a nutrition expert. Always respond with valid JSON only — no markdown, no code fences, no extra text.')
            ->withPrompt($this->buildPrompt($food))
            ->asText()
            ->text;

        $data = json_decode(trim($response), true);

        if (!is_array($data) || !array_key_exists('calories', $data)) {
            throw new RuntimeException("Unexpected AI response: {$response}");
        }

        return [
            'calories'    => max(0, (int) ($data['calories'] ?? 0)),
            'protein'     => max(0, (int) ($data['protein'] ?? 0)),
            'carbs'       => max(0, (int) ($data['carbs'] ?? 0)),
            'fat'         => max(0, (int) ($data['fat'] ?? 0)),
            'explanation' => isset($data['explanation']) ? (string) $data['explanation'] : null,
        ];
    }

    private function buildPrompt(string $food): string
    {
        return <<<PROMPT
        Estimate the nutrition for the following food input and return a JSON object.

        Required schema:
        {"calories": integer, "protein": integer, "carbs": integer, "fat": integer, "explanation": string}

        All numeric fields are integers. Protein, carbs, and fat are in grams.

        Rules:
        - Any edible item, meal, quantity, or restaurant reference is valid food.
        - Treat branded meals and fast food as valid (even if misspelled or regional).
        - If quantity or preparation details are missing, assume a standard single serving.
        - If a known food item is mentioned, estimate it regardless of brand name.
        - The explanation must: (1) state the assumption made about serving size or preparation, (2) mention in one short sentence what extra detail would improve accuracy.

        Examples:
        Input: 4 chicken breasts
        Output: {"calories": 800, "protein": 120, "carbs": 0, "fat": 20, "explanation": "Assumed medium grilled chicken breasts (~200g each). Specify size or cooking method for a better estimate."}

        Input: large Big Mac meal
        Output: {"calories": 1100, "protein": 45, "carbs": 120, "fat": 44, "explanation": "Assumed standard large Big Mac meal with large fries and a medium soft drink. Swapping the drink would change this significantly."}

        Input: {$food}
        Output:
        PROMPT;
    }
}
