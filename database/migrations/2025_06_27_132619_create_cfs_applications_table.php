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
        Schema::create('cfs_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_for_speakers_id')->constrained()->onDelete('cascade');
            $table->foreignId('community_event_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('event_session_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('topic_title');
            $table->text('topic_description');
            $table->text('topic_outline')->nullable();
            $table->enum('experience_level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();
            $table->text('previous_speaking_experience')->nullable();
            $table->json('preferred_sessions')->nullable();
            $table->json('custom_responses')->nullable();
            $table->json('attachments')->nullable();
            $table->enum('status', ['pending', 'under_review', 'approved', 'rejected', 'withdrawn'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->datetime('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('uid')->unique();
            $table->timestamps();

            $table->index(['call_for_speakers_id', 'status']);
            $table->index(['applicant_email', 'status']);
            $table->index(['event_session_id', 'status']);
            $table->index('status');
            $table->unique(['call_for_speakers_id', 'applicant_email', 'event_session_id'], 'unique_session_application');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfs_applications');
    }
};
