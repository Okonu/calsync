<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CallForSpeakers extends Model
{
    use HasFactory;

    protected $table = 'call_for_speakers';

    protected $fillable = [
        'community_id',
        'title',
        'slug',
        'description',
        'guidelines',
        'opens_at',
        'closes_at',
        'is_public',
        'requires_login',
        'show_application_count',
        'allow_multiple_applications',
        'application_type',
        'required_fields',
        'custom_questions',
        'status',
        'auto_approve',
        'acceptance_email_template',
        'rejection_email_template',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'is_public' => 'boolean',
        'requires_login' => 'boolean',
        'show_application_count' => 'boolean',
        'allow_multiple_applications' => 'boolean',
        'required_fields' => 'array',
        'custom_questions' => 'array',
        'auto_approve' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cfs) {
            if (empty($cfs->slug)) {
                $cfs->slug = Str::slug($cfs->title);

                $originalSlug = $cfs->slug;
                $counter = 1;
                while (static::where('community_id', $cfs->community_id)
                    ->where('slug', $cfs->slug)->exists()) {
                    $cfs->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommunityEvent::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CfsApplication::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getPublicUrlAttribute()
    {
        return route('community.cfs.show', [
            'community' => $this->community->slug,
            'cfs' => $this->slug
        ]);
    }

    public function getTotalApplicationsAttribute()
    {
        return $this->applications()->count();
    }

    public function getPendingApplicationsAttribute()
    {
        return $this->applications()->where('status', 'pending')->count();
    }

    public function getApprovedApplicationsAttribute()
    {
        return $this->applications()->where('status', 'approved')->count();
    }

    public function isOpen()
    {
        if ($this->status !== 'open') {
            return false;
        }

        $now = now();

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    public function acceptsApplications()
    {
        return $this->isOpen() && $this->is_public;
    }

    public function canUserApply($email)
    {
        if (!$this->acceptsApplications()) {
            return false;
        }

        if (!$this->allow_multiple_applications) {
            return !$this->applications()
                ->where('applicant_email', $email)
                ->exists();
        }

        return true;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('opens_at')
                    ->orWhere('opens_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('closes_at')
                    ->orWhere('closes_at', '>=', now());
            });
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
