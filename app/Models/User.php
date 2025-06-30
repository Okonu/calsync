<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function googleAccounts(): HasMany
    {
        return $this->hasMany(GoogleAccount::class);
    }

    public function bookingPage()
    {
        return $this->hasOne(BookingPage::class);
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, BookingPage::class);
    }

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function activeCommunities(): HasMany
    {
        return $this->hasMany(Community::class)->where('is_active', true);
    }

    public function communityEvents()
    {
        return $this->hasManyThrough(CommunityEvent::class, Community::class);
    }

    public function callsForSpeakers()
    {
        return $this->hasManyThrough(CallForSpeakers::class, Community::class);
    }

    public function ownsCommunity(Community $community): bool
    {
        return $this->id === $community->user_id;
    }

    public function canManageCommunity(Community $community): bool
    {
        // TODO: to be extended for multi-admin support later
        return $this->ownsCommunity($community);
    }

    public function hasCommunitiesFeature(): bool
    {
        //TODO: to be extended for premium use
        return true;
    }
}
