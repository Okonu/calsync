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
        Schema::table('community_events', function (Blueprint $table) {
            $table->string('meeting_platform')->nullable()->after('meeting_link')
                ->comment('Platform used for online meetings: google_meet, zoom, teams, webex, discord, custom');

            $table->string('google_calendar_event_id')->nullable()->after('meeting_platform')
                ->comment('Google Calendar event ID for automatic calendar integration');

            $table->string('google_calendar_id')->nullable()->after('google_calendar_event_id')
                ->comment('Google Calendar ID where the event was created');

            $table->index(['google_calendar_event_id', 'google_calendar_id'], 'idx_google_calendar_events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_events', function (Blueprint $table) {
            $table->dropIndex('idx_google_calendar_events');
            $table->dropColumn([
                'meeting_platform',
                'google_calendar_event_id',
                'google_calendar_id'
            ]);
        });
    }
};
