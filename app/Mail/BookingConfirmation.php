<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingConfirmation extends Mailable implements ShouldQueue
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
        $cancellationLink = url('/book/cancel/' . $this->booking->uid);
        $subject = $this->isOrganizer
            ? "New booking: Meeting with {$this->booking->name}"
            : "Meeting confirmed with {$this->booking->bookingPage->user->name}";

        return $this->subject($subject)
            ->markdown('emails.booking.confirmation', [
                'booking' => $this->booking,
                'isOrganizer' => $this->isOrganizer,
                'cancellationLink' => $cancellationLink,
            ]);
    }
}
