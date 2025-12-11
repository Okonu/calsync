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
        Schema::table('calendars', function (Blueprint $table) {
            $table->foreignId('google_account_id')->nullable()->change();
            $table->string('google_id')->nullable()->change();
            $table->foreignId('microsoft_account_id')->nullable()->after('google_account_id')->constrained()->onDelete('cascade');
            $table->string('microsoft_id')->nullable()->after('google_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table) {
            $table->dropForeign(['microsoft_account_id']);
            $table->dropColumn(['microsoft_account_id', 'microsoft_id']);
            $table->foreignId('google_account_id')->nullable(false)->change();
            $table->string('google_id')->nullable(false)->change();
        });
    }
};
