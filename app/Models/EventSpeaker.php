<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSpeaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_event_id',
        'event_session_id',
        'cfs_application_id',
        'name',
        'email',
        'phone',
        'bio',
        'photo',
        'company',
        'job_title',
        'topic_title',
        'topic_description',
        'social_links',
        'assignment_type',
        'status',
        'is_featured',
        'sort_order',
        'notes',
        'confirmed_at',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_featured' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($speaker) {
            if ($speaker->eventSession && $speaker->status === 'confirmed') {
                $speaker->eventSession->increment('current_speakers');
                $speaker->eventSession->updateStatus();
            }
        });

        static::updated(function ($speaker) {
            if ($speaker->eventSession && $speaker->isDirty('status')) {
                if ($speaker->status === 'confirmed' && $speaker->getOriginal('status') !== 'confirmed') {
                    $speaker->eventSession->increment('current_speakers');
                } elseif ($speaker->status !== 'confirmed' && $speaker->getOriginal('status') === 'confirmed') {
                    $speaker->eventSession->decrement('current_speakers');
                }
                $speaker->eventSession->updateStatus();
            }
        });

        static::deleted(function ($speaker) {
            if ($speaker->eventSession && $speaker->status === 'confirmed') {
                $speaker->eventSession->decrement('current_speakers');
                $speaker->eventSession->updateStatus();
            }
        });
    }

    public function communityEvent(): BelongsTo
    {
        return $this->belongsTo(CommunityEvent::class);
    }

    public function eventSession(): BelongsTo
    {
        return $this->belongsTo(EventSession::class);
    }

    public function cfsApplication(): BelongsTo
    {
        return $this->belongsTo(CfsApplication::class);
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4285F4&color=fff&size=200';
    }

    public function getDisplayNameAttribute()
    {
        $name = $this->name;

        if ($this->company && $this->job_title) {
            $name .= ' - ' . $this->job_title . ' at ' . $this->company;
        } elseif ($this->company) {
            $name .= ' - ' . $this->company;
        } elseif ($this->job_title) {
            $name .= ' - ' . $this->job_title;
        }

        return $name;
    }

    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return $this;
    }

    public function decline()
    {
        $this->update([
            'status' => 'declined',
        ]);

        return $this;
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
