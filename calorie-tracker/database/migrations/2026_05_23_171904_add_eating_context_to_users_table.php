<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('eating_habit')->nullable()->after('goal'); // home | out | mix
            $table->text('health_notes')->nullable()->after('eating_habit');
            $table->text('ai_explanation')->nullable()->after('health_notes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['eating_habit', 'health_notes', 'ai_explanation']);
        });
    }
};
