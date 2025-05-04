<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'slug',
        'duration',
        'destination_calendar_id',
        'available_days',
        'start_time',
        'end_time',
        'buffer_before',
        'buffer_after',
        'include_meet',
        'selected_calendars',
    ];

    protected $casts = [
        'available_days' => 'array',
        'selected_calendars' => 'array',
        'include_meet' => 'boolean',
    ];

    protected $attributes = [
        'available_days' => '[1,2,3,4,5]',
        'selected_calendars' => '[]',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function destinationCalendar()
    {
        return $this->belongsTo(Calendar::class, 'destination_calendar_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getEffectiveDestinationCalendar()
    {
        if ($this->destination_calendar_id) {
            return Calendar::find($this->destination_calendar_id);
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
}
