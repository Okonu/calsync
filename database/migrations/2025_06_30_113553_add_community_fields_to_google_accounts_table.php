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
        Schema::table('google_accounts', function (Blueprint $table) {
            $table->string('account_type')->nullable()->after('is_primary');
            $table->foreignId('community_id')->nullable()->constrained()->after('account_type');
        });
    }

    public function down(): void
    {
        Schema::table('google_accounts', function (Blueprint $table) {
            $table->dropForeign(['community_id']);
            $table->dropColumn(['account_type', 'community_id']);
        });
    }
};
