<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MicrosoftAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'avatar',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'color',
        'is_active',
        'is_primary',
        'account_type',
        'community_id',
        'last_synced_at',
        'sync_failed',
        'sync_error',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'sync_failed' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at->isPast();
    }

    public function isPersonalAccount(): bool
    {
        return $this->account_type === 'personal' || $this->account_type === null;
    }

    public function isCommunityAccount(): bool
    {
        return $this->account_type === 'community';
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->isCommunityAccount() && $this->community) {
            return $this->community->name . ' (Community)';
        }

        return $this->name;
    }

    public function scopePersonal($query)
    {
        return $query->where(function ($q) {
            $q->where('account_type', 'personal')
                ->orWhereNull('account_type');
        });
    }

    public function scopeCommunity($query)
    {
        return $query->where('account_type', 'community');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)->personal();
    }

    public function scopeForUserCommunities($query, $userId)
    {
        return $query->community()
            ->whereHas('community', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
    }
}
