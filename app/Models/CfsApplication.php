<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CfsApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_for_speakers_id',
        'community_event_id',
        'event_session_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'bio',
        'topic_title',
        'topic_description',
        'topic_outline',
        'experience_level',
        'previous_speaking_experience',
        'preferred_sessions',
        'custom_responses',
        'attachments',
        'status',
        'admin_notes',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
        'uid',
    ];

    protected $casts = [
        'preferred_sessions' => 'array',
        'custom_responses' => 'array',
        'attachments' => 'array',
        'reviewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            if (empty($application->uid)) {
                $application->uid = Str::uuid();
            }
        });

        static::created(function ($application) {
            if ($application->eventSession && $application->eventSession->block_on_application) {
                $application->eventSession->updateStatus();
            }
        });

        static::updated(function ($application) {
            if ($application->eventSession && $application->isDirty('status')) {
                $application->eventSession->updateStatus();
            }
        });

        static::deleted(function ($application) {
            if ($application->eventSession) {
                $application->eventSession->updateStatus();
            }
        });
    }

    public function callForSpeakers(): BelongsTo
    {
        return $this->belongsTo(CallForSpeakers::class);
    }

    public function communityEvent(): BelongsTo
    {
        return $this->belongsTo(CommunityEvent::class);
    }

    public function eventSession(): BelongsTo
    {
        return $this->belongsTo(EventSession::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function approve($reviewerId = null, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'admin_notes' => $notes,
        ]);

        $this->createSpeakerRecord();

        return $this;
    }

    public function reject($reason = null, $reviewerId = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
        ]);

        return $this;
    }

    protected function createSpeakerRecord()
    {
        EventSpeaker::create([
            'community_event_id' => $this->community_event_id,
            'event_session_id' => $this->event_session_id,
            'cfs_application_id' => $this->id,
            'name' => $this->applicant_name,
            'email' => $this->applicant_email,
            'phone' => $this->applicant_phone,
            'bio' => $this->bio,
            'topic_title' => $this->topic_title,
            'topic_description' => $this->topic_description,
            'assignment_type' => 'cfs',
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        if ($this->eventSession) {
            $this->eventSession->increment('current_speakers');
            $this->eventSession->updateStatus();
        }
    }

    public function getPublicTrackingUrlAttribute()
    {
        return route('cfs.application.track', $this->uid);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
