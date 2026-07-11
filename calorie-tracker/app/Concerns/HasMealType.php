<?php

namespace App\Concerns;

use App\Models\Entry;

/**
 * A selectable meal slot (breakfast / lunch / dinner / snack), pre-selected by
 * time of day so it's zero-friction. Shared by the log page (what you're
 * logging) and the dashboard (what kind of meal to suggest).
 */
trait HasMealType
{
    public string $mealType = '';

    public function setMealType(string $type): void
    {
        if (in_array($type, Entry::MEAL_TYPES, true)) {
            $this->mealType = $type;
        }
    }

    protected function defaultMealType(): string
    {
        return Entry::mealTypeForHour(now()->hour);
    }

    /** The selected meal type, guarded against tampering, with a sane fallback. */
    protected function currentMealType(): string
    {
        return in_array($this->mealType, Entry::MEAL_TYPES, true)
            ? $this->mealType
            : $this->defaultMealType();
    }
}
