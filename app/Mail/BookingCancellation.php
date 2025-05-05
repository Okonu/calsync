<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingCancellation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;
    public $isOrganizer;

    public function __construct(Booking $booking, bool $isOrganizer)
    {
        $this->booking = $booking;
        $this->isOrganizer = $isOrganizer;
    }

    public function build()
    {
        $subject = $this->isOrganizer
            ? "Booking cancelled: Meeting with {$this->booking->name}"
            : "Meeting with {$this->booking->bookingPage->user->name} cancelled";

        return $this->subject($subject)
            ->markdown('emails.booking.cancellation', [
                'booking' => $this->booking,
                'isOrganizer' => $this->isOrganizer,
            ]);
    }
}
