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
        Schema::create('telemetry_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp');
            
            // Queue metrics
            $table->integer('queue_pending')->default(0);
            $table->integer('recordings_processed')->default(0);
            $table->integer('recordings_uploaded')->default(0);
            
            // Bandwidth metrics
            $table->decimal('bandwidth_mb', 10, 2)->default(0);
            
            // Quality metrics
            $table->decimal('avg_confidence', 5, 2)->default(0);
            
            // Storage metrics
            $table->decimal('disk_usage_percent', 5, 2)->default(0);
            
            // Full data snapshot
            $table->json('data');
            
            $table->timestamp('created_at');
            
            // Indexes for querying
            $table->index('timestamp');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemetry_logs');
    }
};
