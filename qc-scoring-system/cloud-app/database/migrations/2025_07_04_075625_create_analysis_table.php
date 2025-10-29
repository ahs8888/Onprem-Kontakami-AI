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
        Schema::create('analysis', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('prompt_id')->index();
            $table->integer('recording_id')->index();
            $table->integer('process_id')->index();
            $table->string('foldername');
            $table->string('prompt_name');
            $table->json('prompt');
            $table->integer('total_token')->default(0);
            $table->integer('total_file');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('status', 10)->default('progress');
            $table->boolean('is_new')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis');
    }
};
