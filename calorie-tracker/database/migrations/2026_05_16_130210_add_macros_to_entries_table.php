<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->unsignedSmallInteger('protein')->default(0)->after('calories');
            $table->unsignedSmallInteger('carbs')->default(0)->after('protein');
            $table->unsignedSmallInteger('fat')->default(0)->after('carbs');
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn(['protein', 'carbs', 'fat']);
        });
    }
};
