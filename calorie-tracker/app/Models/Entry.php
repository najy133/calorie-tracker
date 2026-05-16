<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'food',
        'calories',
        'protein',
        'carbs',
        'fat',
    ];

    protected $casts = [
        'calories' => 'integer',
        'protein'  => 'integer',
        'carbs'    => 'integer',
        'fat'      => 'integer',
    ];
}
