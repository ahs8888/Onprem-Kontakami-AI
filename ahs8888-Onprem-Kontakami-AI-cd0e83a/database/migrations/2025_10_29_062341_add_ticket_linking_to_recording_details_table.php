<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add ticket linking fields to recording_details table
     * for linking recordings to ticket system
     */
    public function up(): void
    {
        Schema::table('recording_details', function (Blueprint $table) {
            // Display name for human-readable identification
            $table->string('display_name', 500)->nullable()->after('name');
            
            // Ticket linking fields
            $table->string('ticket_id', 100)->nullable()->after('display_name');
            $table->text('ticket_url')->nullable()->after('ticket_id');
            $table->string('customer_name', 255)->nullable()->after('ticket_url');
            $table->string('agent_name', 255)->nullable()->after('customer_name');
            $table->string('call_intent', 100)->nullable()->after('agent_name');
            $table->string('call_outcome', 100)->nullable()->after('call_intent');
            
            // Linking control flags
            $table->boolean('requires_ticket')->default(true)->after('status')->comment('Does this recording need ticket linking?');
            $table->timestamp('linked_at')->nullable()->after('requires_ticket')->comment('When ticket was linked');
            
            // Indexes for performance
            $table->index('ticket_id', 'idx_recording_details_ticket_id');
            $table->index('status', 'idx_recording_details_status');
            $table->index(['requires_ticket', 'ticket_id'], 'idx_recording_details_requires_ticket');
            
            // Full-text search index for searching by display name, customer, ticket
            $table->fullText(['display_name', 'customer_name', 'ticket_id'], 'idx_recording_details_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recording_details', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_recording_details_ticket_id');
            $table->dropIndex('idx_recording_details_status');
            $table->dropIndex('idx_recording_details_requires_ticket');
            $table->dropFullText('idx_recording_details_search');
            
            // Drop columns
            $table->dropColumn([
                'display_name',
                'ticket_id',
                'ticket_url',
                'customer_name',
                'agent_name',
                'call_intent',
                'call_outcome',
                'requires_ticket',
                'linked_at'
            ]);
        });
    }
};
