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
        Schema::create('community_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->onDelete('cascade');
            $table->foreignId('call_for_speakers_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('type', ['webinar', 'workshop', 'study_jam', 'meetup', 'conference', 'other'])->default('meetup');
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->boolean('is_online')->default(true);
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_settings')->nullable();
            $table->integer('max_attendees')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->boolean('is_public')->default(true);
            $table->text('speaker_requirements')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();

            $table->unique(['community_id', 'slug']);
            $table->index(['community_id', 'status', 'is_public']);
            $table->index(['starts_at', 'status']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_events');
    }
};
