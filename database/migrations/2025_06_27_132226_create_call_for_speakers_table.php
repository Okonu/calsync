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
        Schema::create('call_for_speakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('guidelines')->nullable();
            $table->datetime('opens_at')->nullable();
            $table->datetime('closes_at')->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('requires_login')->default(false);
            $table->boolean('show_application_count')->default(false);
            $table->boolean('allow_multiple_applications')->default(false);
            $table->enum('application_type', ['event', 'session', 'both'])->default('session');
            $table->json('required_fields')->nullable();
            $table->json('custom_questions')->nullable();
            $table->enum('status', ['draft', 'open', 'closed', 'archived'])->default('draft');
            $table->boolean('auto_approve')->default(false);
            $table->text('acceptance_email_template')->nullable();
            $table->text('rejection_email_template')->nullable();
            $table->timestamps();

            $table->unique(['community_id', 'slug']);
            $table->index(['community_id', 'status', 'is_public']);
            $table->index(['opens_at', 'closes_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_for_speakers');
    }
};
