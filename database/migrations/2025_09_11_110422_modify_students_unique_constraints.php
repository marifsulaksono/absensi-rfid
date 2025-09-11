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
        Schema::table('students', function (Blueprint $table) {
            // Drop the existing unique constraints
            $table->dropUnique(['nis']);
            $table->dropUnique(['rfid_number']);
            
            // Add new unique constraints that consider soft deletes
            $table->unique(['nis', 'deleted_at']);
            $table->unique(['rfid_number', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Drop the composite unique constraints
            $table->dropUnique(['nis', 'deleted_at']);
            $table->dropUnique(['rfid_number', 'deleted_at']);
            
            // Restore the original unique constraints
            $table->unique(['nis']);
            $table->unique(['rfid_number']);
        });
    }
};
