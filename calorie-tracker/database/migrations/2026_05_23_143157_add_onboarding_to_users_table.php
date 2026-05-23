<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('daily_goal');
            $table->string('sex', 1)->nullable()->after('age');
            $table->decimal('weight_kg', 5, 2)->nullable()->after('sex');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('weight_kg');
            $table->string('activity_level', 16)->nullable()->after('height_cm');
            $table->string('goal', 16)->nullable()->after('activity_level');
            $table->timestamp('onboarded_at')->nullable()->after('goal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age', 'sex', 'weight_kg', 'height_cm', 'activity_level', 'goal', 'onboarded_at']);
        });
    }
};
