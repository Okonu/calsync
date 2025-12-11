<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CommunityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id',
        'call_for_speakers_id',
        'title',
        'slug',
        'description',
        'type',
        'starts_at',
        'ends_at',
        'location',
        'meeting_link',
        'meeting_platform',
        'google_calendar_event_id',
        'google_calendar_id',
        'is_online',
        'is_recurring',
        'recurrence_settings',
        'max_attendees',
        'requires_approval',
        'status',
        'is_public',
        'speaker_requirements',
        'custom_fields',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_online' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_settings' => 'array',
        'requires_approval' => 'boolean',
        'is_public' => 'boolean',
        'custom_fields' => 'array',
    ];

    public const MEETING_PLATFORMS = [
        'google_meet' => 'Google Meet',
        'zoom' => 'Zoom',
        'teams' => 'Microsoft Teams',
        'webex' => 'Cisco Webex',
        'discord' => 'Discord',
        'custom' => 'Other Platform',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);

                $originalSlug = $event->slug;
                $counter = 1;
                while (static::where('community_id', $event->community_id)
                    ->where('slug', $event->slug)->exists()) {
                    $event->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function callForSpeakers(): BelongsTo
    {
        return $this->belongsTo(CallForSpeakers::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EventSession::class);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(EventSpeaker::class);
    }

    public function cfsApplications(): HasMany
    {
        return $this->hasMany(CfsApplication::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getPublicUrlAttribute()
    {
        // Try to get the slug from the loaded relationship first
        if ($this->community) {
            $communitySlug = $this->community->slug;
        } else {
            // Fallback: query the database for the community slug
            $communitySlug = Community::where('id', $this->community_id)->value('slug');
        }

        return route('community.events.show.public', [
            'slug' => $communitySlug,
            'event' => $this->slug
        ]);
    }

    public function getTotalSpeakersAttribute()
    {
        return $this->speakers()->where('status', 'confirmed')->count();
    }

    public function getAvailableSessionsAttribute()
    {
        return $this->sessions()
            ->where('status', 'available')
            ->where('allows_applications', true)
            ->count();
    }

    public function getMeetingPlatformNameAttribute()
    {
        if (!$this->meeting_platform) {
            return null;
        }

        return static::MEETING_PLATFORMS[$this->meeting_platform] ?? 'Meeting Platform';
    }

    public function usesGoogleMeet(): bool
    {
        return $this->meeting_platform === 'google_meet';
    }

    public function hasGoogleCalendarEvent(): bool
    {
        return !empty($this->google_calendar_event_id);
    }

    public function getGoogleCalendarEventUrlAttribute()
    {
        if (!$this->hasGoogleCalendarEvent()) {
            return null;
        }

        return "https://calendar.google.com/calendar/event?eid={$this->google_calendar_event_id}";
    }

    public function requiresMeetingLink(): bool
    {
        return $this->is_online &&
            $this->meeting_platform &&
            $this->meeting_platform !== 'google_meet';
    }

    public function getMeetingInstructionsAttribute()
    {
        if (!$this->is_online || !$this->meeting_link) {
            return null;
        }

        $instructions = [
            'zoom' => 'Click the meeting link to join via Zoom. You may need to download the Zoom app if joining for the first time.',
            'teams' => 'Click the meeting link to join via Microsoft Teams. You can join from your browser or the Teams app.',
            'webex' => 'Click the meeting link to join via Cisco Webex. You can join from your browser or the Webex app.',
            'discord' => 'Click the invite link to join our Discord server. You\'ll need a Discord account.',
            'google_meet' => 'Click the Google Meet link to join the meeting. You can join from your browser or the Meet app.',
            'custom' => 'Click the meeting link to join.',
        ];

        return $instructions[$this->meeting_platform] ?? $instructions['custom'];
    }

    public function getMeetingPlatformIconAttribute()
    {
        $icons = [
            'google_meet' => '📹',
            'zoom' => '🔵',
            'teams' => '💜',
            'webex' => '🟢',
            'discord' => '🎮',
            'custom' => '🔗',
        ];

        return $icons[$this->meeting_platform] ?? '🔗';
    }

    public function acceptsApplications()
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->callForSpeakers) {
            return $this->callForSpeakers->status === 'open';
        }

        return $this->sessions()
            ->where('allows_applications', true)
            ->where('status', 'available')
            ->exists();
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true)
            ->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now())
            ->orderBy('starts_at');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopePhysical($query)
    {
        return $query->where('is_online', false);
    }

    public function scopeWithMeetingPlatform($query, $platform)
    {
        return $query->where('meeting_platform', $platform);
    }

    public function scopeWithGoogleCalendarEvent($query)
    {
        return $query->whereNotNull('google_calendar_event_id');
    }
}
