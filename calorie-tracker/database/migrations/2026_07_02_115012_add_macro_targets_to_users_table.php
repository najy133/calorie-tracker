<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('target_protein')->nullable()->after('daily_goal');
            $table->unsignedSmallInteger('target_carbs')->nullable()->after('target_protein');
            $table->unsignedSmallInteger('target_fat')->nullable()->after('target_carbs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['target_protein', 'target_carbs', 'target_fat']);
        });
    }
};
