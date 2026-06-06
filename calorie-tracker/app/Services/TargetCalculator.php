<?php

namespace App\Services;

class TargetCalculator
{
    private const ACTIVITY_MULTIPLIERS = [
        'sedentary' => 1.2,
        'light'     => 1.375,
        'moderate'  => 1.55,
        'very'      => 1.725,
    ];

    private const GOAL_DELTAS = [
        'lose'     => -500,
        'maintain' => 0,
        'build'    => 300,
    ];

    private const MACRO_RATIOS = [
        'lose'     => ['protein' => 0.32, 'carbs' => 0.40, 'fat' => 0.28],
        'maintain' => ['protein' => 0.28, 'carbs' => 0.45, 'fat' => 0.27],
        'build'    => ['protein' => 0.30, 'carbs' => 0.50, 'fat' => 0.20],
    ];

    public function calculate(int $age, string $sex, float $weightKg, int $heightCm, string $activity, string $goal): array
    {
        $bmr = $sex === 'M'
            ? 10 * $weightKg + 6.25 * $heightCm - 5 * $age + 5
            : 10 * $weightKg + 6.25 * $heightCm - 5 * $age - 161;

        $multiplier = self::ACTIVITY_MULTIPLIERS[$activity] ?? 1.2;
        $tdee       = $bmr * $multiplier;
        $delta      = self::GOAL_DELTAS[$goal] ?? 0;
        $target     = (int) (round(($tdee + $delta) / 10) * 10);

        return [
            'bmr'    => (int) round($bmr),
            'tdee'   => (int) round($tdee),
            'target' => $target,
        ];
    }

    public function macroSplit(int $target, string $goal): array
    {
        $ratios = self::MACRO_RATIOS[$goal] ?? self::MACRO_RATIOS['maintain'];

        return [
            'protein' => (int) round($target * $ratios['protein'] / 4),
            'carbs'   => (int) round($target * $ratios['carbs'] / 4),
            'fat'     => (int) round($target * $ratios['fat'] / 9),
        ];
    }
}
