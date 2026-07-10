<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            // Every per-user/per-day query (today's totals, streak, weekly
            // aggregate, history) filters on these; SQLite doesn't auto-index
            // the user_id foreign key.
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
