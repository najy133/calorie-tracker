<?php

namespace App\Concerns;

use App\Models\Entry;

trait HasStreak
{
    private function calculateStreak(): int
    {
        if (!auth()->check()) return 0;

        $streak = 0;
        $day    = today();

        while (Entry::where('user_id', auth()->id())->whereDate('created_at', $day)->exists()) {
            $streak++;
            $day = $day->subDay();
        }

        return $streak;
    }
}
