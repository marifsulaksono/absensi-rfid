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
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nis', 30)->unique();
            $table->string('name', 250);
            $table->string('address');
            $table->date('birthday');
            $table->string('phone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('rfid_number', 100)->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('rfid_number');
            $table->index('nis');
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
