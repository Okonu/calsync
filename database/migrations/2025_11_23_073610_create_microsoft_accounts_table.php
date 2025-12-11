<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('microsoft_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email')->unique(); // Microsoft email might not be unique across different providers if we allow multiple, but for now unique is safe per table
            $table->string('name');
            $table->string('color');
            $table->string('avatar')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable(); // Microsoft refresh tokens might be null in some flows? Better safe.
            $table->timestamp('token_expires_at');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false);
            $table->string('account_type')->nullable();
            $table->foreignId('community_id')->nullable()->constrained();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('sync_failed')->default(false);
            $table->text('sync_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('microsoft_accounts');
    }
};
