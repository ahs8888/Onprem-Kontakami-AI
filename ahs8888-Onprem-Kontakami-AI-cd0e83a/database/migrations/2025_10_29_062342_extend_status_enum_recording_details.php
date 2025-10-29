<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Extend status enum to include ticket linking statuses
     */
    public function up(): void
    {
        // For MySQL, we need to use raw SQL to modify enum
        DB::statement("
            ALTER TABLE recording_details 
            MODIFY COLUMN status ENUM(
                'Progress', 
                'Success', 
                'Failed', 
                'unlinked', 
                'linked', 
                'no_ticket_needed'
            ) NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("
            ALTER TABLE recording_details 
            MODIFY COLUMN status ENUM(
                'Progress', 
                'Success', 
                'Failed'
            ) NULL
        ");
    }
};
