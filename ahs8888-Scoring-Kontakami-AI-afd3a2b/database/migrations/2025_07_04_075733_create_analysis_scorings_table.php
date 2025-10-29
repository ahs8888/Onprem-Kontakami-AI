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
        Schema::create('analysis_scorings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('analysis_id')->constrained('analysis')->cascadeOnDelete();
            $table->integer('recording_file_id')->index();
            $table->integer('request_ai_id')->nullable()->index();
            $table->string('filename');
            $table->string('file_size',20);
            $table->longText('scoring')->nullable();
            $table->double('avg_score');
            $table->longText('non_scoring')->nullable();
            $table->json('summary')->nullable();
            $table->longText('transcribe');
            $table->integer('token');
            $table->boolean('is_done')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_scorings');
    }
};
