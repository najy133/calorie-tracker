<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Global spend guard for OpenAI calls.
 *
 * Every AI service calls guard() right before it hits the network. It counts
 * calls per calendar month in the cache and throws once the configured cap is
 * reached. Because each service already treats a thrown/failed AI call as a
 * graceful degradation (Homepage shows a retry message, MealSuggester returns
 * no ideas, AITargetAdvisor falls back to the formula target), hitting the cap
 * never white-screens a user — it just pauses AI features until next month.
 */
class AiBudget
{
    public static function guard(): void
    {
        $cap = (int) config('ai.monthly_call_cap', 0);

        // 0 (or negative) means "no cap" — skip the bookkeeping entirely.
        if ($cap <= 0) {
            return;
        }

        $key = self::currentKey();

        if ((int) Cache::get($key, 0) >= $cap) {
            throw new RuntimeException('Monthly AI budget reached.');
        }

        // Seed the counter (only if missing) with a TTL that comfortably
        // outlives the month, then increment. The key rolls over on its own
        // each month, so the count resets without a scheduled job.
        Cache::add($key, 0, now()->addDays(40));
        Cache::increment($key);
    }

    private static function currentKey(): string
    {
        return 'ai:calls:' . now()->format('Y-m');
    }
}
