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
        Schema::create('presents', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_student');
            $table->foreign('id_student')->references('id')->on('students')->onDelete('cascade');
            $table->date('date');
            $table->time('in');
            $table->time('out')->nullable();
            $table->boolean('is_displayed')->default(false);
            $table->unique(['id_student', 'date']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presents');
    }
};
