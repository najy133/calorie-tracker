<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    /** Meal slots, in the order they're shown. */
    public const MEAL_TYPES = ['breakfast', 'lunch', 'dinner', 'snack'];

    protected $fillable = [
        'user_id',
        'food',
        'calories',
        'protein',
        'carbs',
        'fat',
        'source',    // 'ai' (estimated) | 'manual' (user-entered)
        'meal_type', // breakfast | lunch | dinner | snack
    ];

    /** The meal slot a given time of day falls into. */
    public static function mealTypeForHour(int $hour): string
    {
        return match (true) {
            $hour < 11 => 'breakfast',
            $hour < 16 => 'lunch',
            $hour < 21 => 'dinner',
            default    => 'snack',
        };
    }

    protected $casts = [
        'calories' => 'integer',
        'protein'  => 'integer',
        'carbs'    => 'integer',
        'fat'      => 'integer',
    ];
}
