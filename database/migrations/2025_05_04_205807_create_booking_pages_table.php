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
        Schema::create('booking_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->integer('duration')->default(30);
            $table->foreignId('destination_calendar_id')->nullable()->constrained('calendars');
            $table->json('available_days')->nullable();
            $table->time('start_time')->default('09:00');
            $table->time('end_time')->default('17:00');
            $table->integer('buffer_before')->nullable();
            $table->integer('buffer_after')->nullable();
            $table->boolean('include_meet')->default(true);
            $table->json('selected_calendars')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_pages');
    }
};
