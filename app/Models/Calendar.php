<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_account_id',
        'google_id',
        'microsoft_account_id',
        'microsoft_id',
        'name',
        'color',
        'is_visible',
        'is_primary',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_primary' => 'boolean',
    ];

    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    public function microsoftAccount(): BelongsTo
    {
        return $this->belongsTo(MicrosoftAccount::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
