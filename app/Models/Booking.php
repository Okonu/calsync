<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_page_id',
        'calendar_id',
        'name',
        'email',
        'starts_at',
        'ends_at',
        'notes',
        'status',
        'uid',
        'google_event_id',
        'meeting_link',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function bookingPage()
    {
        return $this->belongsTo(BookingPage::class);
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }
}
