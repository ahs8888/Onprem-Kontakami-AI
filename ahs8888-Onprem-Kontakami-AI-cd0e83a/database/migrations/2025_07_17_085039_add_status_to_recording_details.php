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
        Schema::table('recording_details', function (Blueprint $table) {
            $table->enum("status", ['Progress', 'Success', 'Failed'])->after("name")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recording_details', function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }
};
