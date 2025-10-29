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
        Schema::create('agent_scoring_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_scoring_id')->constrained('agent_scorings')->cascadeOnDelete();
            $table->integer('request_ai_id')->nullable()->index();
            $table->string('agent');
            $table->string('spv');
            $table->integer('total_file');
            $table->json('scoring')->nullable();
            $table->double('avg_score');
            $table->json('summary_temp')->nullable();
            $table->json('summary')->nullable();
            $table->json('recording_files_id')->nullable();
            $table->json('analysis_scorings_id')->nullable();
            $table->json('files')->nullable();
            $table->integer('token');
            $table->boolean('is_done')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_scoring_items');
    }
};
