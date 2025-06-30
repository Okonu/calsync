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
        Schema::create('event_speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_session_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('cfs_application_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('topic_title')->nullable();
            $table->text('topic_description')->nullable();
            $table->json('social_links')->nullable();
            $table->enum('assignment_type', ['cfs', 'manual', 'invited'])->default('manual');
            $table->enum('status', ['confirmed', 'tentative', 'declined', 'cancelled'])->default('confirmed');
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->datetime('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['community_event_id', 'status']);
            $table->index(['event_session_id', 'status']);
            $table->index('assignment_type');
            $table->unique(['community_event_id', 'event_session_id', 'email'], 'unique_speaker_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_speakers');
    }
};
