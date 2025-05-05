<!-- resources/views/emails/booking/cancellation.blade.php -->
@component('mail::message')
    # Meeting Cancelled

    <div style="text-align: center; margin-bottom: 25px;">
        <div style="display: inline-block; background-color: #fef2f2; border-radius: 8px; padding: 20px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
            <h2 style="color: #b91c1c; margin-top: 0; margin-bottom: 15px; font-size: 20px;">Meeting Cancelled</h2>

            <p style="color: #4b5563; margin-bottom: 20px;">
                @if($isOrganizer)
                    {{ $booking->name }} has cancelled their meeting with you.
                @else
                    Your meeting with {{ $booking->bookingPage->user->name }} has been cancelled.
                @endif
            </p>

            <div style="background-color: #ffffff; border-radius: 6px; padding: 15px; margin-bottom: 20px; text-align: left; border-left: 4px solid #ef4444;">
                <h3 style="margin-top: 0; margin-bottom: 10px; color: #111827; font-size: 16px;">Cancelled Meeting Details</h3>
                <p style="margin: 0; color: #374151; font-size: 14px; margin-bottom: 6px;"><strong>Date:</strong> {{ $booking->starts_at->format('l, F j, Y') }}</p>
                <p style="margin: 0; color: #374151; font-size: 14px; margin-bottom: 6px;"><strong>Time:</strong> {{ $booking->starts_at->format('g:i A') }} - {{ $booking->ends_at->format('g:i A') }} ({{ $booking->starts_at->timezone->getName() }})</p>
                <p style="margin: 0; color: #374151; font-size: 14px;"><strong>Duration:</strong> {{ $booking->bookingPage->duration }} minutes</p>
            </div>
        </div>
    </div>

    @if(!$isOrganizer)
        ## Would you like to reschedule?
        If you'd like to book another meeting, please click the button below:

        @component('mail::button', ['url' => url('/book/' . $booking->bookingPage->slug), 'color' => 'primary'])
            Book Another Meeting
        @endcomponent
    @endif

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
