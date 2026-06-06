<?php

use App\Services\TargetCalculator;

it('calculates target calories for a known sample', function () {
    $calc   = new TargetCalculator();
    $result = $calc->calculate(28, 'M', 74, 178, 'moderate', 'maintain');

    // BMR = 10*74 + 6.25*178 - 5*28 + 5 = 740 + 1112.5 - 140 + 5 = 1717.5
    // TDEE = 1717.5 * 1.55 = 2662.125 → rounded to nearest 10 = 2660
    expect($result['bmr'])->toBe(1718);
    expect($result['target'])->toBe(2660);
});

it('calculates macros for maintain goal', function () {
    $calc   = new TargetCalculator();
    $macros = $calc->macroSplit(2660, 'maintain');

    expect($macros['protein'])->toBeInt();
    expect($macros['carbs'])->toBeInt();
    expect($macros['fat'])->toBeInt();

    // protein = round(2660 * 0.28 / 4) = round(186.2) = 186
    expect($macros['protein'])->toBe(186);
});

it('applies correct goal delta for lose', function () {
    $calc   = new TargetCalculator();
    $result = $calc->calculate(28, 'M', 74, 178, 'moderate', 'lose');

    // TDEE ~2662, -500 = 2162 → rounded to nearest 10 = 2160
    expect($result['target'])->toBe(2160);
});
