<?php

namespace App\Concerns;

use App\Models\Entry;

trait HasStreak
{
    /**
     * Consecutive days (ending today) with at least one logged meal.
     *
     * One query: pull the distinct logged dates, then walk them in PHP —
     * instead of firing one EXISTS query per day of the streak.
     */
    private function calculateStreak(): int
    {
        if (!auth()->check()) return 0;

        $loggedDays = Entry::where('user_id', auth()->id())
            ->selectRaw('DATE(created_at) as day')
            ->distinct()
            ->orderByDesc('day')
            ->pluck('day')
            ->flip(); // ['Y-m-d' => index] for O(1) lookup

        $streak = 0;
        $day    = today();

        while ($loggedDays->has($day->toDateString())) {
            $streak++;
            $day = $day->subDay();
        }

        return $streak;
    }
}
