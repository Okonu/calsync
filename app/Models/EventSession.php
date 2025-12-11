<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_event_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'max_speakers',
        'current_speakers',
        'allows_applications',
        'block_on_application',
        'status',
        'location',
        'meeting_link',
        'requirements',
        'custom_fields',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'allows_applications' => 'boolean',
        'block_on_application' => 'boolean',
        'custom_fields' => 'array',
    ];

    public function communityEvent(): BelongsTo
    {
        return $this->belongsTo(CommunityEvent::class);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(EventSpeaker::class);
    }

    public function cfsApplications(): HasMany
    {
        return $this->hasMany(CfsApplication::class);
    }

    public function getAvailableSpotsAttribute()
    {
        return max(0, $this->max_speakers - $this->current_speakers);
    }

    public function getIsFullAttribute()
    {
        return $this->current_speakers >= $this->max_speakers;
    }

    public function getHasPendingApplicationsAttribute()
    {
        return $this->cfsApplications()
            ->where('status', 'pending')
            ->exists();
    }

    public function canAcceptApplications()
    {
        if (!$this->allows_applications || $this->status !== 'available') {
            return false;
        }

        if ($this->is_full) {
            return false;
        }

        if ($this->block_on_application && $this->has_pending_applications) {
            return false;
        }

        return true;
    }

    public function updateStatus()
    {
        if ($this->is_full) {
            $this->status = 'full';
        } elseif ($this->has_pending_applications && $this->block_on_application) {
            $this->status = 'pending';
        } elseif ($this->current_speakers > 0) {
            $this->status = 'confirmed';
        } else {
            $this->status = 'available';
        }

        $this->save();
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('allows_applications', true);
    }

    public function scopeAcceptingApplications($query)
    {
        return $query->where('allows_applications', true)
            ->where('status', 'available')
            ->whereRaw('current_speakers < max_speakers');
    }
}
