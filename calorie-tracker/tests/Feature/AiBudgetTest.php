<?php

use App\Support\AiBudget;
use Illuminate\Support\Facades\Cache;

it('allows calls up to the monthly cap then blocks', function () {
    config(['ai.monthly_call_cap' => 3]);
    Cache::flush();

    // First three calls are within budget.
    AiBudget::guard();
    AiBudget::guard();
    AiBudget::guard();

    // The fourth trips the cap.
    expect(fn () => AiBudget::guard())
        ->toThrow(RuntimeException::class);
});

it('never blocks when the cap is disabled', function () {
    config(['ai.monthly_call_cap' => 0]);
    Cache::flush();

    for ($i = 0; $i < 50; $i++) {
        AiBudget::guard();
    }

    expect(true)->toBeTrue(); // reached here without throwing
});
