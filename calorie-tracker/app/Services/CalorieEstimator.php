<?php

namespace App\Services;

use Prism\Prism\Facades\Prism;
use Prism\Prism\Enums\Provider;
use RuntimeException;

class CalorieEstimator
{
    /**
     * @return array{calories: int, explanation: string|null}
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
            'calories' => max(0, (int) $data['calories']),
            'explanation' => isset($data['explanation']) ? (string) $data['explanation'] : null,
        ];
    }

    private function buildPrompt(string $food): string
    {
        return <<<PROMPT
        Estimate the calories for the following food input and return a JSON object.

        Required schema:
        {"calories": integer, "explanation": string}

        Rules:
        - Any edible item, meal, quantity, or restaurant reference is valid food.
        - Treat branded meals and fast food as valid (even if misspelled or regional).
        - If quantity or preparation details are missing, assume a standard single serving.
        - If a known food item is mentioned, estimate it regardless of brand name.
        - The explanation must: (1) state the assumption made about serving size or preparation, (2) mention in one short sentence what extra detail would improve accuracy.

        Examples:
        Input: 4 chicken breasts
        Output: {"calories": 800, "explanation": "Assumed medium grilled chicken breasts (~200g each). Specify size or cooking method for a better estimate."}

        Input: large Big Mac meal
        Output: {"calories": 1100, "explanation": "Assumed standard large Big Mac meal with large fries and a medium soft drink. Swapping the drink would change this significantly."}

        Input: {$food}
        Output:
        PROMPT;
    }
}
