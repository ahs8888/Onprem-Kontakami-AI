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
            // AI Quality Scores
            $table->decimal('confidence_score', 5, 2)->nullable()->after('status');
            $table->decimal('quality_score', 5, 2)->nullable()->after('confidence_score');
            
            // PII Detection
            $table->boolean('pii_detected')->default(false)->after('quality_score');
            $table->json('pii_types')->nullable()->after('pii_detected');
            
            // Upload Decision
            $table->enum('upload_decision', ['pending', 'approved', 'rejected', 'review'])->default('pending')->after('pii_types');
            $table->text('decision_reason')->nullable()->after('upload_decision');
            
            // Retry tracking
            $table->integer('retry_count')->default(0)->after('decision_reason');
            $table->timestamp('last_retry_at')->nullable()->after('retry_count');
            
            // Encryption tracking
            $table->boolean('is_encrypted')->default(false)->after('last_retry_at');
            $table->string('encryption_algorithm', 50)->nullable()->after('is_encrypted');
            
            // Bandwidth tracking
            $table->bigInteger('original_size_bytes')->nullable()->after('encryption_algorithm');
            $table->bigInteger('encrypted_size_bytes')->nullable()->after('original_size_bytes');
            $table->bigInteger('uploaded_size_bytes')->nullable()->after('encrypted_size_bytes');
            
            // Processing metadata
            $table->json('cleaning_metadata')->nullable()->after('uploaded_size_bytes');
            $table->timestamp('processed_at')->nullable()->after('cleaning_metadata');
            
            // Add indexes for performance
            $table->index('confidence_score');
            $table->index('quality_score');
            $table->index('upload_decision');
            $table->index('pii_detected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recording_details', function (Blueprint $table) {
            $table->dropIndex(['confidence_score']);
            $table->dropIndex(['quality_score']);
            $table->dropIndex(['upload_decision']);
            $table->dropIndex(['pii_detected']);
            
            $table->dropColumn([
                'confidence_score',
                'quality_score',
                'pii_detected',
                'pii_types',
                'upload_decision',
                'decision_reason',
                'retry_count',
                'last_retry_at',
                'is_encrypted',
                'encryption_algorithm',
                'original_size_bytes',
                'encrypted_size_bytes',
                'uploaded_size_bytes',
                'cleaning_metadata',
                'processed_at'
            ]);
        });
    }
};
