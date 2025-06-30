<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'contact_email',
        'calendar_email',
        'destination_calendar_id',
        'google_account_id',
        'social_links',
        'timezone',
        'color',
        'is_public',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($community) {
            if (empty($community->slug)) {
                $community->slug = Str::slug($community->name);

                $originalSlug = $community->slug;
                $counter = 1;
                while (static::where('slug', $community->slug)->exists()) {
                    $community->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommunityEvent::class);
    }

    public function callsForSpeakers(): HasMany
    {
        return $this->hasMany(CallForSpeakers::class);
    }

    public function speakers(): HasMany
    {
        return $this->hasMany(EventSpeaker::class, 'community_event_id');
    }

    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    public function destinationCalendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class, 'destination_calendar_id');
    }

    public function calendar()
    {
        return $this->hasOneThrough(
            Calendar::class,
            GoogleAccount::class,
            'user_id',
            'google_account_id',
            'user_id',
            'id'
        )->where('calendars.name', 'like', '%' . $this->name . '%')
            ->orWhere('calendars.is_primary', true);
    }

    public function getEffectiveDestinationCalendar()
    {
        if ($this->destination_calendar_id) {
            return Calendar::find($this->destination_calendar_id);
        }

        if ($this->google_account_id) {
            return Calendar::where('google_account_id', $this->google_account_id)
                ->where('is_primary', true)
                ->first();
        }

        $primaryAccount = GoogleAccount::where('user_id', $this->user_id)
            ->where('is_primary', true)
            ->first();

        if (!$primaryAccount) {
            $primaryAccount = GoogleAccount::where('user_id', $this->user_id)
                ->where('is_active', true)
                ->first();
        }

        if ($primaryAccount) {
            return Calendar::where('google_account_id', $primaryAccount->id)
                ->where('is_primary', true)
                ->first();
        }

        return null;
    }

    public function getAvailableCalendars()
    {
        $accountIds = collect();

        if ($this->google_account_id) {
            $accountIds->push($this->google_account_id);
        }

        $userAccounts = GoogleAccount::where('user_id', $this->user_id)
            ->where('is_active', true)
            ->pluck('id');

        $accountIds = $accountIds->merge($userAccounts);

        return Calendar::whereIn('google_account_id', $accountIds->unique())
            ->with('googleAccount:id,name,email,is_primary')
            ->get();
    }

    public function getEffectiveEventCalendar()
    {
        if ($this->destination_calendar_id) {
            $calendar = Calendar::find($this->destination_calendar_id);
            if ($calendar && $calendar->googleAccount->is_active) {
                return $calendar;
            }
        }

        if ($this->google_account_id) {
            $calendar = Calendar::where('google_account_id', $this->google_account_id)
                ->where('is_primary', true)
                ->first();

            if ($calendar) {
                return $calendar;
            }

            $calendar = Calendar::where('google_account_id', $this->google_account_id)
                ->first();

            if ($calendar) {
                return $calendar;
            }
        }

        $primaryAccount = GoogleAccount::where('user_id', $this->user_id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->first();

        if ($primaryAccount) {
            return Calendar::where('google_account_id', $primaryAccount->id)
                ->where('is_primary', true)
                ->first();
        }

        return null;
    }

    public function getAvailabilityCalendars()
    {
        if (empty($this->availability_calendars)) {
            $userAccounts = GoogleAccount::where('user_id', $this->user_id)
                ->where('is_active', true)
                ->pluck('id');

            return Calendar::whereIn('google_account_id', $userAccounts)
                ->where('is_visible', true)
                ->get();
        }

        return Calendar::whereIn('id', $this->availability_calendars)
            ->with('googleAccount')
            ->get();
    }

    public function hasCalendarIntegration()
    {
        return !empty($this->calendar_email) || !empty($this->google_account_id);
    }

    public function getCalendarAccount()
    {
        if ($this->google_account_id) {
            return GoogleAccount::find($this->google_account_id);
        }

        if ($this->calendar_email) {
            return GoogleAccount::where('user_id', $this->user_id)
                ->where('email', $this->calendar_email)
                ->where('is_active', true)
                ->first();
        }

        return null;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getPublicUrlAttribute()
    {
        return route('community.show', $this->slug);
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=' . ltrim($this->color, '#') . '&color=fff&size=200';
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true)->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->where('is_active', true);
    }
}
