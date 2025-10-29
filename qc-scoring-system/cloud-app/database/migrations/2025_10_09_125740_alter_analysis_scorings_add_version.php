<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analysis_scorings', function (Blueprint $table) {
            $table->enum('sentiment', ["Sangat Negatif", "Negatif", "Netral", "Positif", "Sangat Positif"])->after('summary')->nullable();
            $table->smallInteger('version')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
