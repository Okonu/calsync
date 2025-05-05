<!-- resources/views/emails/booking/confirmation.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isOrganizer ? 'New Booking' : 'Meeting Confirmed' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #374151;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .card {
            text-align: center;
            margin-bottom: 25px;
        }
        .card-inner {
            display: inline-block;
            background-color: #f0f9ff;
            border-radius: 8px;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .card-title {
            color: #1e40af;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .card-text {
            color: #4b5563;
            margin-bottom: 20px;
        }
        .details-box {
            background-color: #ffffff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
            border-left: 4px solid #4f46e5;
        }
        .details-title {
            margin-top: 0;
            margin-bottom: 10px;
            color: #111827;
            font-size: 16px;
        }
        .details-item {
            margin: 0;
            color: #374151;
            font-size: 14px;
            margin-bottom: 6px;
        }
        .notes-box {
            background-color: #ffffff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
        }
        .section-title {
            color: #111827;
            font-size: 18px;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 500;
            margin: 10px 0;
        }
        .button-red {
            background-color: #ef4444;
        }
        .footer {
            margin-top: 30px;
            text-align: left;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $isOrganizer ? 'New Booking' : 'Meeting Confirmed' }}</h1>
    </div>

    <div class="card">
        <div class="card-inner">
            <h2 class="card-title">Meeting Confirmed</h2>

            <p class="card-text">
                @if($isOrganizer)
                    {{ $booking->name }} has booked a meeting with you.
                @else
                    Your meeting with {{ $booking->bookingPage->user->name }} has been confirmed.
                @endif
            </p>

            <div class="details-box">
                <h3 class="details-title">Meeting Details</h3>
                <p class="details-item"><strong>Date:</strong> {{ $booking->starts_at->format('l, F j, Y') }}</p>
                <p class="details-item"><strong>Time:</strong> {{ $booking->starts_at->format('g:i A') }} - {{ $booking->ends_at->format('g:i A') }} ({{ $booking->starts_at->timezone->getName() }})</p>
                <p class="details-item"><strong>Duration:</strong> {{ $booking->bookingPage->duration }} minutes</p>
            </div>

            @if($booking->notes)
                <div class="notes-box">
                    <h3 class="details-title">Additional Notes</h3>
                    <p style="margin: 0; color: #4b5563; font-size: 14px;">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    @if($booking->meeting_link)
        <h2 class="section-title">Join Meeting</h2>
        <p>You can join the meeting using this link:</p>
        <a href="{{ $booking->meeting_link }}" class="button">Join Meeting</a>
    @endif

    @if(!$isOrganizer)
        <h2 class="section-title">Need to make changes?</h2>
        <p>If you need to cancel this meeting, please click the button below:</p>
        <a href="{{ $cancellationLink }}" class="button button-red">Cancel Meeting</a>
    @endif

    <div class="footer">
        <p>Thanks,<br>
            {{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>
