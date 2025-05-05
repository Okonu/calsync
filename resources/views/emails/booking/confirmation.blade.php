<!-- resources/views/emails/booking/confirmation.blade.php -->
@component('mail::message')
    # {{ $isOrganizer ? 'New Booking' : 'Meeting Confirmed' }}

    <div style="text-align: center; margin-bottom: 25px;">
        <div style="display: inline-block; background-color: #f0f9ff; border-radius: 8px; padding: 20px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
            <h2 style="color: #1e40af; margin-top: 0; margin-bottom: 15px; font-size: 20px;">Meeting Confirmed</h2>

            <p style="color: #4b5563; margin-bottom: 20px;">
                @if($isOrganizer)
                    {{ $booking->name }} has booked a meeting with you.
                @else
                    Your meeting with {{ $booking->bookingPage->user->name }} has been confirmed.
                @endif
            </p>

            <div style="background-color: #ffffff; border-radius: 6px; padding: 15px; margin-bottom: 20px; text-align: left; border-left: 4px solid #4f46e5;">
                <h3 style="margin-top: 0; margin-bottom: 10px; color: #111827; font-size: 16px;">Meeting Details</h3>
                <p style="margin: 0; color: #374151; font-size: 14px; margin-bottom: 6px;"><strong>Date:</strong> {{ $booking->starts_at->format('l, F j, Y') }}</p>
                <p style="margin: 0; color: #374151; font-size: 14px; margin-bottom: 6px;"><strong>Time:</strong> {{ $booking->starts_at->format('g:i A') }} - {{ $booking->ends_at->format('g:i A') }} ({{ $booking->starts_at->timezone->getName() }})</p>
                <p style="margin: 0; color: #374151; font-size: 14px;"><strong>Duration:</strong> {{ $booking->bookingPage->duration }} minutes</p>
            </div>

            @if($booking->notes)
                <div style="background-color: #ffffff; border-radius: 6px; padding: 15px; margin-bottom: 20px; text-align: left;">
                    <h3 style="margin-top: 0; margin-bottom: 10px; color: #111827; font-size: 16px;">Additional Notes</h3>
                    <p style="margin: 0; color: #4b5563; font-size: 14px;">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($booking->meeting_link)
        ## Join Meeting
        You can join the meeting using this link:

        @component('mail::button', ['url' => $booking->meeting_link, 'color' => 'primary'])
            Join Meeting
        @endcomponent
    @endif

    @if(!$isOrganizer)
        ## Need to make changes?
        If you need to cancel this meeting, please click the button below:

        @component('mail::button', ['url' => $cancellationLink, 'color' => 'red'])
            Cancel Meeting
        @endcomponent
    @endif

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
