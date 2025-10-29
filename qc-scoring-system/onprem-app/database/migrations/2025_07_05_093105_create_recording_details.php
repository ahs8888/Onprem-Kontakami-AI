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
        Schema::create('recording_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid("recording_id");
            $table->foreign('recording_id')->references('id')->on('recordings')->onDelete('cascade');
            $table->string("name");
            $table->string("file")->nullable();
            $table->longText("transcript")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_details');
    }
};
