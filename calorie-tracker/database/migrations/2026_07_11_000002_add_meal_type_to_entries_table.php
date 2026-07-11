<?php

use App\Models\Entry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->string('meal_type')->nullable()->after('source');
        });

        // Backfill existing entries from the time they were logged, so the
        // grouped history reads as if meal type had always been tracked.
        DB::table('entries')->whereNull('meal_type')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $hour = (int) \Illuminate\Support\Carbon::parse($row->created_at)->format('G');
                DB::table('entries')->where('id', $row->id)
                    ->update(['meal_type' => Entry::mealTypeForHour($hour)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('meal_type');
        });
    }
};
