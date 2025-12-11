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
        Schema::create('event_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_event_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->integer('max_speakers')->default(1);
            $table->integer('current_speakers')->default(0);
            $table->boolean('allows_applications')->default(true);
            $table->boolean('block_on_application')->default(true);
            $table->enum('status', ['available', 'pending', 'confirmed', 'full', 'cancelled'])->default('available');
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->text('requirements')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->index(['community_event_id', 'status']);
            $table->index(['starts_at', 'status']);
            $table->index('allows_applications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_sessions');
    }
};
