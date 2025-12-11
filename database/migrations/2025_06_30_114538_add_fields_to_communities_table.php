<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->string('calendar_email')->nullable()->after('contact_email');
            $table->unsignedBigInteger('destination_calendar_id')->nullable()->after('calendar_email');
            $table->unsignedBigInteger('google_account_id')->nullable()->after('destination_calendar_id');

            $table->foreign('destination_calendar_id')->references('id')->on('calendars')->onDelete('set null');
            $table->foreign('google_account_id')->references('id')->on('google_accounts')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropForeign(['destination_calendar_id']);
            $table->dropForeign(['google_account_id']);
            $table->dropColumn(['calendar_email', 'destination_calendar_id', 'google_account_id']);
        });
    }
};
