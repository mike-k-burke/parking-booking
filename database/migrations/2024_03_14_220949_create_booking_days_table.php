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
        Schema::create('booking_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->date('date')->constrained('calendar_days', 'date');
            $table->unsignedInteger('price');
            $table->timestamps();

            $table->unique(['booking_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_days');
    }
};
